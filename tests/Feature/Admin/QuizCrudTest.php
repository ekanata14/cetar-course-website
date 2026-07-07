<?php

use App\Livewire\Admin\Quiz\Index;
use App\Livewire\Admin\Quiz\Questions;
use App\Models\Package;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

test('quizzes page renders for super admin', function () {
    $this->actingAs($this->admin)
        ->get('/admin/quizzes')
        ->assertOk();
});

test('super admin can create a quiz attached to packages', function () {
    $packages = Package::factory()->count(2)->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openCreate')
        ->set('title', 'Try Out SKD #99')
        ->set('durationMinutes', 100)
        ->set('packageIds', $packages->pluck('id')->all())
        ->call('save')
        ->assertHasNoErrors();

    $quiz = Quiz::where('title', 'Try Out SKD #99')->first();

    expect($quiz)->not->toBeNull()
        ->and($quiz->packages)->toHaveCount(2);
});

test('updating a quiz syncs its package distribution', function () {
    $quiz = Quiz::factory()->create();
    [$old, $new] = Package::factory()->count(2)->create();
    $quiz->packages()->attach($old);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openEdit', $quiz->id)
        ->set('packageIds', [$new->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($quiz->refresh()->packages()->pluck('packages.id')->all())->toBe([$new->id]);
});

test('deleting a quiz removes its pivot rows and questions', function () {
    $quiz = Quiz::factory()->create();
    $package = Package::factory()->create();
    $quiz->packages()->attach($package);
    $question = Question::factory()->for($quiz)->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('delete', $quiz->id);

    expect(Quiz::find($quiz->id))->toBeNull()
        ->and(Question::find($question->id))->toBeNull()
        ->and($package->quizzes()->count())->toBe(0);
});

test('questions page renders and lists sections', function () {
    $quiz = Quiz::factory()->create();
    Question::factory()->for($quiz)->create(['section' => 'TWK']);
    Question::factory()->for($quiz)->create(['section' => 'TIU']);

    $this->actingAs($this->admin)
        ->get(route('admin.quizzes.questions', $quiz))
        ->assertOk()
        ->assertSee('TWK')
        ->assertSee('TIU');
});

test('super admin can add a question to a quiz', function () {
    $quiz = Quiz::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Questions::class, ['quiz' => $quiz])
        ->call('openCreate')
        ->set('section', 'TWK')
        ->set('text', 'Siapa proklamator kemerdekaan Indonesia?')
        ->set('optionA', 'Soekarno-Hatta')
        ->set('optionB', 'Sudirman')
        ->set('optionC', 'Diponegoro')
        ->set('optionD', 'Kartini')
        ->set('correctAnswer', 'A')
        ->set('points', 5)
        ->call('save')
        ->assertHasNoErrors();

    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions->first()->correct_answer)->toBe('A');
});

test('correct answer cannot be E when option E is empty', function () {
    $quiz = Quiz::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Questions::class, ['quiz' => $quiz])
        ->call('openCreate')
        ->set('text', 'Pertanyaan uji validasi?')
        ->set('optionA', 'A')->set('optionB', 'B')->set('optionC', 'C')->set('optionD', 'D')
        ->set('optionE', '')
        ->set('correctAnswer', 'E')
        ->call('save')
        ->assertHasErrors(['correctAnswer']);
});

test('super admin can edit and delete a question', function () {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->for($quiz)->create(['text' => 'Teks lama?']);

    $component = Livewire::actingAs($this->admin)
        ->test(Questions::class, ['quiz' => $quiz])
        ->call('openEdit', $question->id)
        ->set('text', 'Teks baru?')
        ->call('save')
        ->assertHasNoErrors();

    expect($question->refresh()->text)->toBe('Teks baru?');

    $component->call('delete', $question->id);
    expect(Question::find($question->id))->toBeNull();
});
