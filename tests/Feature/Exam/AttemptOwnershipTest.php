<?php

use App\Actions\Quiz\SaveUserAnswer;
use App\Actions\Quiz\SubmitQuizAttempt;
use App\Enums\AttemptStatus;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizAttempt;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Helper: attempt in-progress milik seorang user.
 */
function inProgressAttemptFor(User $user): UserQuizAttempt
{
    $quiz = Quiz::factory()->create(['duration_minutes' => 60]);
    Question::factory()->count(2)->for($quiz)->create(['points' => 5]);

    return $user->quizAttempts()->create([
        'quiz_id' => $quiz->id,
        'started_at' => now(),
        'status' => AttemptStatus::InProgress,
    ]);
}

test('another user cannot save answers into someone else\'s attempt', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $attempt = inProgressAttemptFor($owner);
    $question = $attempt->quiz->questions->first();

    $this->actingAs($intruder);

    $saved = app(SaveUserAnswer::class)->execute($attempt, $question->id, 'A');

    expect($saved)->toBeFalse()
        ->and($attempt->answers()->count())->toBe(0);
});

test('the owner can still save answers normally', function () {
    $owner = User::factory()->create();
    $attempt = inProgressAttemptFor($owner);
    $question = $attempt->quiz->questions->first();

    $this->actingAs($owner);

    expect(app(SaveUserAnswer::class)->execute($attempt, $question->id, 'A'))->toBeTrue()
        ->and($attempt->answers()->count())->toBe(1);
});

test('another user cannot submit someone else\'s attempt', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $attempt = inProgressAttemptFor($owner);

    $this->actingAs($intruder);

    expect(fn () => app(SubmitQuizAttempt::class)->execute($attempt))
        ->toThrow(AuthorizationException::class);

    expect($attempt->refresh()->status)->toBe(AttemptStatus::InProgress);
});
