<?php

use App\Enums\AttemptStatus;
use App\Livewire\Exam\QuizEngine;
use App\Livewire\Exam\QuizResult;
use App\Models\Package;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizAttempt;
use App\Models\UserSubscription;
use Livewire\Livewire;

/**
 * Helper: user berlangganan aktif + kuis terpasang di roadmap paketnya
 * sebagai item pertama (otomatis terbuka menurut gerbang sekuensial).
 */
function subscribedUserWithQuiz(int $questionCount = 3): array
{
    $user = User::factory()->create();
    $package = Package::factory()->create();
    $quiz = Quiz::factory()->create(['duration_minutes' => 60]);

    $module = $package->modules()->create(['title' => 'Modul 1', 'order' => 1]);
    $module->items()->create([
        'contentable_type' => 'quiz',
        'contentable_id' => $quiz->id,
        'order' => 1,
        'is_locked_by_default' => true, // Item pertama tetap terbuka
    ]);

    Question::factory()->count($questionCount)->for($quiz)->create(['points' => 5]);

    UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'status' => 'active',
        'expires_at' => now()->addMonth(),
    ]);

    return [$user, $quiz];
}

test('users without an active subscription cannot open the exam', function () {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->create();

    $this->actingAs($user)
        ->get(route('user.exam', $quiz))
        ->assertForbidden();
});

test('a subscribed user can open the exam and an attempt is created', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    $this->actingAs($user)
        ->get(route('user.exam', $quiz))
        ->assertOk();

    expect($user->quizAttempts()->where('quiz_id', $quiz->id)->count())->toBe(1);
});

test('reopening the exam resumes the same attempt without resetting the timer', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    $this->actingAs($user)->get(route('user.exam', $quiz));
    $attempt = $user->quizAttempts()->first();
    $originalStart = $attempt->started_at;

    $this->travel(10)->minutes();
    $this->actingAs($user)->get(route('user.exam', $quiz));

    expect($user->quizAttempts()->count())->toBe(1)
        ->and($attempt->refresh()->started_at->equalTo($originalStart))->toBeTrue();
});

test('selecting an answer auto-saves and re-selecting updates the same row', function () {
    [$user, $quiz] = subscribedUserWithQuiz();
    $question = $quiz->questions->first();

    $component = Livewire::actingAs($user)->test(QuizEngine::class, ['quiz' => $quiz]);

    $component->call('selectAnswer', $question->id, 'B');
    $component->call('selectAnswer', $question->id, 'D'); // Ganti jawaban -> upsert, bukan baris baru

    $attempt = $user->quizAttempts()->first();

    expect($attempt->answers()->count())->toBe(1)
        ->and($attempt->answers->first()->selected_option)->toBe('D');
});

test('toggling doubt persists without losing the selected option', function () {
    [$user, $quiz] = subscribedUserWithQuiz();
    $question = $quiz->questions->first();

    Livewire::actingAs($user)
        ->test(QuizEngine::class, ['quiz' => $quiz])
        ->call('selectAnswer', $question->id, 'A')
        ->call('toggleDoubt', $question->id);

    $answer = UserQuizAnswer::first();

    expect($answer->is_doubtful)->toBeTrue()
        ->and($answer->selected_option)->toBe('A');
});

test('answers from other quizzes are rejected', function () {
    [$user, $quiz] = subscribedUserWithQuiz();
    $foreignQuestion = Question::factory()->create(); // Milik kuis lain

    Livewire::actingAs($user)
        ->test(QuizEngine::class, ['quiz' => $quiz])
        ->call('selectAnswer', $foreignQuestion->id, 'A');

    expect(UserQuizAnswer::count())->toBe(0);
});

test('submitting calculates the score and marks answers correct', function () {
    [$user, $quiz] = subscribedUserWithQuiz();
    // Kunci deterministik: semua soal berjawaban 'A', 5 poin
    $quiz->questions()->update(['correct_answer' => 'A', 'points' => 5]);
    [$q1, $q2, $q3] = $quiz->questions()->orderBy('id')->get();

    Livewire::actingAs($user)
        ->test(QuizEngine::class, ['quiz' => $quiz])
        ->call('selectAnswer', $q1->id, 'A')  // benar (+5)
        ->call('selectAnswer', $q2->id, 'B')  // salah
        // q3 tidak dijawab
        ->call('submitQuiz')
        ->assertRedirect();

    $attempt = $user->quizAttempts()->first();

    expect($attempt->status)->toBe(AttemptStatus::Completed)
        ->and($attempt->score)->toBe(5)
        ->and($attempt->completed_at)->not->toBeNull()
        ->and($attempt->answers()->where('question_id', $q1->id)->first()->is_correct)->toBeTrue()
        ->and($attempt->answers()->where('question_id', $q2->id)->first()->is_correct)->toBeFalse();
});

test('sections are tabbed in canonical order and navigation is scoped per section', function () {
    [$user, $quiz] = subscribedUserWithQuiz(0);
    // Buat soal lintas section dengan urutan acak; tab harus tetap TWK → TIU → TKP
    Question::factory()->count(2)->for($quiz)->create(['section' => 'TKP']);
    Question::factory()->count(3)->for($quiz)->create(['section' => 'TWK']);
    Question::factory()->count(4)->for($quiz)->create(['section' => 'TIU']);

    $component = Livewire::actingAs($user)->test(QuizEngine::class, ['quiz' => $quiz]);

    // Urutan tab baku & tab awal = TWK
    expect($component->instance()->sections->all())->toBe(['TWK', 'TIU', 'TKP'])
        ->and($component->get('activeSection'))->toBe('TWK')
        // Grid section aktif hanya berisi soal TWK (3 soal)
        ->and($component->instance()->sectionQuestions)->toHaveCount(3);

    // Pindah ke TIU: reset index, grid berisi 4 soal TIU
    $component->call('setSection', 'TIU');
    expect($component->get('activeSection'))->toBe('TIU')
        ->and($component->get('currentIndex'))->toBe(0)
        ->and($component->instance()->sectionQuestions)->toHaveCount(4);

    // goTo dibatasi jumlah soal section aktif (indeks 3 valid, 4 ditolak)
    $component->call('goTo', 3);
    expect($component->get('currentIndex'))->toBe(3);
    $component->call('goTo', 4);
    expect($component->get('currentIndex'))->toBe(3);

    // Section tak dikenal diabaikan
    $component->call('setSection', 'ZZZ');
    expect($component->get('activeSection'))->toBe('TIU');
});

test('opening an expired attempt auto-submits and redirects to the result', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    // Attempt dimulai jauh melewati durasi kuis (60 menit)
    $attempt = UserQuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'started_at' => now()->subHours(2),
        'status' => AttemptStatus::InProgress,
    ]);

    Livewire::actingAs($user)
        ->test(QuizEngine::class, ['quiz' => $quiz])
        ->assertRedirect(route('user.exam.result', $attempt));

    expect($attempt->refresh()->status)->toBe(AttemptStatus::Completed);
});

test('answers are rejected after the time limit', function () {
    [$user, $quiz] = subscribedUserWithQuiz();
    $question = $quiz->questions->first();

    $attempt = UserQuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'started_at' => now()->subHours(2), // Sudah lewat deadline
        'status' => AttemptStatus::InProgress,
    ]);

    $saved = app(\App\Actions\Quiz\SaveUserAnswer::class)
        ->execute($attempt, $question->id, 'A');

    expect($saved)->toBeFalse()
        ->and(UserQuizAnswer::count())->toBe(0);
});

test('the exam payload never exposes the answer key', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    $component = Livewire::actingAs($user)->test(QuizEngine::class, ['quiz' => $quiz]);

    // Kolom sensitif tidak ikut ter-select pada payload soal
    $component->instance()->questions->each(function ($question) {
        expect($question->getAttributes())
            ->not->toHaveKey('correct_answer')
            ->not->toHaveKey('explanation');
    });
});

test('the result page shows the score and only the owner can view it', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    $attempt = UserQuizAttempt::factory()->completed()->create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'score' => 10,
    ]);

    $this->actingAs($user)
        ->get(route('user.exam.result', $attempt))
        ->assertOk()
        ->assertSee('10');

    // User lain (dengan langganan valid pun) tidak boleh melihat hasil orang lain
    $intruder = User::factory()->create();
    $this->actingAs($intruder)
        ->get(route('user.exam.result', $attempt))
        ->assertForbidden();
});

test('an in-progress attempt result redirects back to the exam room', function () {
    [$user, $quiz] = subscribedUserWithQuiz();

    $attempt = UserQuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'started_at' => now(),
        'status' => AttemptStatus::InProgress,
    ]);

    Livewire::actingAs($user)
        ->test(QuizResult::class, ['attempt' => $attempt])
        ->assertRedirect(route('user.exam', $quiz->id));
});
