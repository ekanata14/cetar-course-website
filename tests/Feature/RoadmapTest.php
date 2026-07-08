<?php

use App\Actions\Roadmap\MarkItemComplete;
use App\Actions\Roadmap\ResolveJourney;
use App\Livewire\Admin\Package\RoadmapBuilder;
use App\Livewire\User\MaterialsJourney;
use App\Models\Content;
use App\Models\Package;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserSubscription;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

/**
 * Helper: paket + 1 modul berisi [teks, video, try out] berurutan,
 * plus user berlangganan aktif.
 */
function roadmapFixture(): array
{
    $user = User::factory()->create();
    $package = Package::factory()->create();
    $module = $package->modules()->create(['title' => 'Modul 1', 'order' => 1]);

    $text = Content::create(['title' => 'Pengantar Belajar', 'type' => 'text', 'body' => 'Isi materi.']);
    $video = Content::create(['title' => 'Video Pembahasan', 'type' => 'video', 'video_url' => 'https://youtu.be/abcdefghijk']);
    $quiz = Quiz::factory()->create(['duration_minutes' => 60]);
    Question::factory()->count(2)->for($quiz)->create(['points' => 5]);

    $items = collect([
        ['contentable' => $text, 'type' => 'content'],
        ['contentable' => $video, 'type' => 'content'],
        ['contentable' => $quiz, 'type' => 'quiz'],
    ])->map(fn ($entry, $index) => $module->items()->create([
        'contentable_type' => $entry['type'],
        'contentable_id' => $entry['contentable']->id,
        'order' => $index + 1,
        'is_locked_by_default' => true,
    ]));

    UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'status' => 'active',
        'expires_at' => now()->addMonth(),
    ]);

    return [$user, $package, $items->values(), $quiz];
}

// ==========================================
// ATURAN GERBANG SEKUENSIAL
// ==========================================

test('only the first item starts unlocked', function () {
    [$user, $package] = roadmapFixture();

    $items = app(ResolveJourney::class)->execute($user, $package)
        ->flatMap(fn ($module) => $module->items)->values();

    expect($items[0]->is_unlocked)->toBeTrue()
        ->and($items[1]->is_unlocked)->toBeFalse()
        ->and($items[2]->is_unlocked)->toBeFalse();
});

test('completing an item unlocks the next one', function () {
    [$user, $package, $items] = roadmapFixture();

    app(MarkItemComplete::class)->execute($user, $items[0]);

    $decorated = app(ResolveJourney::class)->execute($user, $package)
        ->flatMap(fn ($module) => $module->items)->values();

    expect($decorated[0]->is_completed)->toBeTrue()
        ->and($decorated[1]->is_unlocked)->toBeTrue()
        ->and($decorated[2]->is_unlocked)->toBeFalse(); // Try out masih menunggu video
});

test('an item flagged as not locked by default is always open', function () {
    [$user, $package, $items] = roadmapFixture();
    $items[2]->update(['is_locked_by_default' => false]);

    $decorated = app(ResolveJourney::class)->execute($user, $package)
        ->flatMap(fn ($module) => $module->items)->values();

    expect($decorated[2]->is_unlocked)->toBeTrue();
});

test('marking a locked item complete is rejected', function () {
    [$user, , $items] = roadmapFixture();

    expect(fn () => app(MarkItemComplete::class)->execute($user, $items[1]))
        ->toThrow(AuthorizationException::class);
});

test('a non subscriber cannot mark progress', function () {
    [, , $items] = roadmapFixture();
    $outsider = User::factory()->create();

    expect(fn () => app(MarkItemComplete::class)->execute($outsider, $items[0]))
        ->toThrow(AuthorizationException::class);
});

test('marking complete is idempotent and keeps the first completion time', function () {
    [$user, , $items] = roadmapFixture();

    $first = app(MarkItemComplete::class)->execute($user, $items[0]);
    $this->travel(10)->minutes();
    $second = app(MarkItemComplete::class)->execute($user, $items[0]);

    expect(UserProgress::count())->toBe(1)
        ->and($second->completed_at->equalTo($first->completed_at))->toBeTrue();
});

// ==========================================
// GERBANG TRY OUT (QUIZ ENGINE)
// ==========================================

test('the exam stays locked until preceding materials are done', function () {
    [$user, , , $quiz] = roadmapFixture();

    $this->actingAs($user)
        ->get(route('user.exam', $quiz))
        ->assertForbidden();
});

test('the exam opens after all preceding materials are completed', function () {
    [$user, , $items, $quiz] = roadmapFixture();

    app(MarkItemComplete::class)->execute($user, $items[0]);
    app(MarkItemComplete::class)->execute($user, $items[1]);

    $this->actingAs($user)
        ->get(route('user.exam', $quiz))
        ->assertOk();
});

test('submitting the quiz auto-completes its roadmap item', function () {
    [$user, $package, $items, $quiz] = roadmapFixture();

    app(MarkItemComplete::class)->execute($user, $items[0]);
    app(MarkItemComplete::class)->execute($user, $items[1]);

    // Buka ujian (membuat attempt) lalu submit
    Livewire::actingAs($user)
        ->test(\App\Livewire\Exam\QuizEngine::class, ['quiz' => $quiz])
        ->call('submitQuiz');

    $progress = UserProgress::where('user_id', $user->id)
        ->where('roadmap_item_id', $items[2]->id)
        ->first();

    expect($progress?->is_completed)->toBeTrue();
});

// ==========================================
// HALAMAN USER
// ==========================================

test('the journey page renders modules with locked and unlocked states', function () {
    [$user, $package] = roadmapFixture();

    $this->actingAs($user)
        ->get(route('user.journey', $package))
        ->assertOk()
        ->assertSee('Modul 1')
        ->assertSee('Pengantar Belajar')
        ->assertSee('Terkunci');
});

test('the journey page is forbidden without a subscription', function () {
    [, $package] = roadmapFixture();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('user.journey', $package))
        ->assertForbidden();
});

test('selecting items in the player respects the sequential gate', function () {
    [$user, $package, $items] = roadmapFixture();

    // Item pertama terbuka → jadi item aktif default; item kedua terkunci → pilihan diabaikan
    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSet('activeItemId', $items[0]->id)
        ->assertSee('Pengantar Belajar')
        ->call('selectItem', $items[1]->id)
        ->assertSet('activeItemId', $items[0]->id);
});

test('a deep link to a locked item falls back to the first open item', function () {
    [$user, $package, $items] = roadmapFixture();

    Livewire::actingAs($user)
        ->withQueryParams(['item' => $items[2]->id])
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSet('activeItemId', $items[0]->id);
});

test('the old learn url redirects into the player with the item active', function () {
    [$user, $package, $items] = roadmapFixture();

    $this->actingAs($user)
        ->get(route('user.learn', $items[0]))
        ->assertRedirect(route('user.journey', ['package' => $package->id, 'item' => $items[0]->id]));
});

test('prev button is null on the first item and populated on subsequent ones', function () {
    [$user, $package, $items] = roadmapFixture();
    app(MarkItemComplete::class)->execute($user, $items[0]);

    // Default aktif = item[1] (pertama yang terbuka & belum selesai)
    $comp = Livewire::actingAs($user)->test(MaterialsJourney::class, ['package' => $package]);

    expect($comp->get('prevItemId'))->toBe($items[0]->id)
        ->and($comp->get('nextItemId'))->toBeNull(); // item[2] masih terkunci
});

test('goToPrev navigates backward without checking the unlock gate', function () {
    [$user, $package, $items] = roadmapFixture();
    app(MarkItemComplete::class)->execute($user, $items[0]);

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSet('activeItemId', $items[1]->id) // default = item[1] (baru terbuka)
        ->call('goToPrev')
        ->assertSet('activeItemId', $items[0]->id);
});

test('goToNext navigates to the next unlocked item', function () {
    [$user, $package, $items] = roadmapFixture();
    app(MarkItemComplete::class)->execute($user, $items[0]); // buka item[1]

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->call('selectItem', $items[0]->id) // mundur ke item pertama
        ->assertSet('activeItemId', $items[0]->id)
        ->call('goToNext')
        ->assertSet('activeItemId', $items[1]->id);
});

test('goToNext does nothing when the next item is locked', function () {
    [$user, $package, $items] = roadmapFixture();
    // Hanya item[0] terbuka; item[1] & [2] masih terkunci

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSet('activeItemId', $items[0]->id)
        ->call('goToNext')
        ->assertSet('activeItemId', $items[0]->id); // tidak berubah
});

test('marking a material complete from the player advances to the next item', function () {
    [$user, $package, $items] = roadmapFixture();

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->call('markAsComplete')
        ->assertSet('activeItemId', $items[1]->id); // Auto-lanjut ke video yang baru terbuka

    expect(UserProgress::where('roadmap_item_id', $items[0]->id)->sole()->is_completed)->toBeTrue();
});

// ==========================================
// TAB TRY OUT + RIWAYAT
// ==========================================

test('the try out tab lists quizzes with attempt history and result link', function () {
    [$user, $package, $items, $quiz] = roadmapFixture();

    // Buka gerbang kuis dulu: selesaikan dua materi sebelumnya
    app(MarkItemComplete::class)->execute($user, $items[0]);
    app(MarkItemComplete::class)->execute($user, $items[1]);

    $attempt = \App\Models\UserQuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'started_at' => now()->subHour(),
        'completed_at' => now()->subMinutes(20),
        'score' => 8,
        'status' => \App\Enums\AttemptStatus::Completed,
    ]);

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSee($quiz->title)
        ->assertSee('Riwayat')
        ->assertSee('Lihat Hasil')
        ->assertSee(route('user.exam.result', $attempt))
        ->assertSee('Skor terbaik');
});

test('a locked quiz in the try out tab shows terkunci without a cta', function () {
    [$user, $package, , $quiz] = roadmapFixture();

    // Semua item terkunci berurutan → kuis (item ke-3) masih terkunci
    $html = Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->html();

    expect($html)->toContain('Selesaikan item sebelumnya untuk membuka try out ini.')
        ->not->toContain(route('user.exam.prepare', $quiz));
});

test('an unlocked quiz without history shows the empty state and kerjakan cta', function () {
    [$user, $package, $items, $quiz] = roadmapFixture();

    app(MarkItemComplete::class)->execute($user, $items[0]);
    app(MarkItemComplete::class)->execute($user, $items[1]);

    Livewire::actingAs($user)
        ->test(MaterialsJourney::class, ['package' => $package])
        ->assertSee('Belum ada riwayat pengerjaan.')
        ->assertSee(route('user.exam.prepare', $quiz));
});

// ==========================================
// ADMIN ROADMAP BUILDER
// ==========================================

test('admin can build a roadmap: add module, add items, reorder, and remove', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $package = Package::factory()->create();
    $content = Content::create(['title' => 'Materi A', 'type' => 'text', 'body' => 'Isi.']);
    $quiz = Quiz::factory()->create();

    $builder = Livewire::actingAs($admin)
        ->test(RoadmapBuilder::class, ['package' => $package])
        ->call('openCreateModule')
        ->set('moduleTitle', 'Modul 1 — Dasar')
        ->call('saveModule')
        ->assertHasNoErrors();

    $module = $package->modules()->sole();
    expect($module->title)->toBe('Modul 1 — Dasar');

    // Tambah materi lalu kuis
    $builder->call('openAddItem', $module->id)
        ->set('itemType', 'content')->set('itemId', (string) $content->id)
        ->call('addItem')->assertHasNoErrors()
        ->call('openAddItem', $module->id)
        ->set('itemType', 'quiz')->set('itemId', (string) $quiz->id)
        ->call('addItem')->assertHasNoErrors();

    $items = $module->items()->get();
    expect($items)->toHaveCount(2)
        ->and($items[0]->contentable_type)->toBe('content')
        ->and($items[1]->contentable_type)->toBe('quiz');

    // Geser kuis ke atas
    $builder->call('moveItem', $items[1]->id, 'up');
    expect($module->items()->get()->first()->contentable_type)->toBe('quiz');

    // Lepas item pertama
    $builder->call('removeItem', $items[1]->id);
    expect($module->items()->count())->toBe(1);
});

test('the roadmap builder page is not reachable by regular users', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create();

    // Middleware role:super_admin mengalihkan non-admin, bukan 403
    $this->actingAs($user)
        ->get(route('admin.packages.roadmap', $package))
        ->assertRedirect();
});
