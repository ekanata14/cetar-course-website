<?php

use App\Livewire\Exam\QuizBriefing;
use App\Models\Package;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizAttempt;
use App\Models\UserSubscription;
use Livewire\Livewire;

/**
 * Helper: user berlangganan + kuis sebagai item pertama roadmap (terbuka).
 */
function briefingFixture(int $questionCount = 3): array
{
    $user = User::factory()->create();
    $package = Package::factory()->create();
    $quiz = Quiz::factory()->create(['title' => 'Try Out Persiapan #1', 'duration_minutes' => 90]);

    $module = $package->modules()->create(['title' => 'Modul 1', 'order' => 1]);
    $module->items()->create([
        'contentable_type' => 'quiz',
        'contentable_id' => $quiz->id,
        'order' => 1,
        'is_locked_by_default' => true,
    ]);

    Question::factory()->count($questionCount)->for($quiz)->create(['points' => 5, 'section' => 'TWK']);

    UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'status' => 'active',
        'expires_at' => now()->addMonth(),
    ]);

    return [$user, $quiz];
}

test('the briefing page shows the exam summary and rules without starting an attempt', function () {
    [$user, $quiz] = briefingFixture();

    $this->actingAs($user)
        ->get(route('user.exam.prepare', $quiz))
        ->assertOk()
        ->assertSee('Try Out Persiapan #1')
        ->assertSee('Peraturan Ujian')
        ->assertSee('90')  // Durasi
        ->assertSee('TWK') // Komposisi section
        ->assertSee('Mulai Ujian');

    // Membuka halaman persiapan TIDAK memulai timer
    expect(UserQuizAttempt::count())->toBe(0);
});

test('the briefing page is forbidden without a subscription', function () {
    [, $quiz] = briefingFixture();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('user.exam.prepare', $quiz))
        ->assertForbidden();
});

test('the briefing page stays locked until preceding materials are done', function () {
    [$user, $quiz] = briefingFixture();

    // Sisipkan materi terkunci di depan kuis: kuis jadi item kedua (terkunci)
    $module = $quiz->roadmapItems()->sole()->module;
    $quiz->roadmapItems()->sole()->update(['order' => 2]);
    $content = \App\Models\Content::create(['title' => 'Materi Pembuka', 'type' => 'text', 'body' => 'Isi.']);
    $module->items()->create([
        'contentable_type' => 'content',
        'contentable_id' => $content->id,
        'order' => 1,
        'is_locked_by_default' => false,
    ]);

    $this->actingAs($user)
        ->get(route('user.exam.prepare', $quiz))
        ->assertForbidden();
});

test('pressing start creates the attempt and enters the exam room', function () {
    [$user, $quiz] = briefingFixture();

    Livewire::actingAs($user)
        ->test(QuizBriefing::class, ['quiz' => $quiz])
        ->call('start')
        ->assertRedirect(route('user.exam', $quiz));

    expect($user->quizAttempts()->inProgress()->where('quiz_id', $quiz->id)->count())->toBe(1);
});

test('an in-progress session shows a resume CTA and start does not reset the timer', function () {
    [$user, $quiz] = briefingFixture();

    // Sesi berjalan dimulai 10 menit lalu
    $attempt = UserQuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'started_at' => now()->subMinutes(10),
        'status' => 'in_progress',
        'score' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('user.exam.prepare', $quiz))
        ->assertOk()
        ->assertSee('Lanjutkan Ujian')
        ->assertSee('sesi yang sedang berjalan');

    Livewire::actingAs($user)
        ->test(QuizBriefing::class, ['quiz' => $quiz])
        ->call('start')
        ->assertRedirect(route('user.exam', $quiz));

    // Attempt lama dilanjutkan, started_at tidak berubah (timer tidak direset)
    expect(UserQuizAttempt::count())->toBe(1)
        ->and((int) $attempt->refresh()->started_at->diffInMinutes(now()))->toBe(10);
});

test('the journey timeline links quizzes to the briefing page', function () {
    [$user, $quiz] = briefingFixture();
    $package = $quiz->roadmapItems()->sole()->module->package;

    $this->actingAs($user)
        ->get(route('user.journey', $package))
        ->assertOk()
        ->assertSee(route('user.exam.prepare', $quiz), false);
});
