<?php

use App\Enums\SubscriptionStatus;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizAttempt;
use App\Models\UserSubscription;
use Database\Seeders\DatabaseSeeder;

it('seeds the foundation data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::where('role', 'super_admin')->count())->toBe(1)
        ->and(User::count())->toBe(12) // 1 admin + 10 demo users + 1 demo pejuang (demo@cetar.id)
        ->and(Package::count())->toBe(2)
        ->and(PackagePlan::count())->toBe(4)
        ->and(Quiz::count())->toBe(2)
        ->and(Question::count())->toBeGreaterThan(0);

    // Setiap user punya kode referral unik
    expect(User::whereNull('referral_code')->count())->toBe(0);
});

it('walks the subscription and payment relations', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create();
    $plan = PackagePlan::factory()->for($package)->create();

    $payment = Payment::create([
        'user_id' => $user->id,
        'package_plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => 'settled',
        'external_id' => 'INV-TEST-001',
    ]);

    expect($payment->packagePlan->package->is($package))->toBeTrue()
        ->and($user->payments()->count())->toBe(1)
        ->and($package->plans()->count())->toBe(1);
});

it('distributes quizzes to packages via the polymorphic pivot', function () {
    $package = Package::factory()->create();
    $quiz = Quiz::factory()->create();

    $package->quizzes()->attach($quiz);

    expect($package->quizzes()->count())->toBe(1)
        ->and($quiz->packages()->first()->is($package))->toBeTrue();
});

it('scopes active subscriptions by status and expiry', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create();

    // Aktif dan belum kedaluwarsa -> masuk scope
    UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'status' => SubscriptionStatus::Active,
        'expires_at' => now()->addDays(30),
    ]);

    // Status aktif tapi sudah lewat expires_at -> tidak masuk scope
    $expiredPackage = Package::factory()->create();
    UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $expiredPackage->id,
        'status' => SubscriptionStatus::Active,
        'expires_at' => now()->subDay(),
    ]);

    expect(UserSubscription::active()->count())->toBe(1)
        ->and($user->hasActiveSubscription($package))->toBeTrue()
        ->and($user->hasActiveSubscription($expiredPackage))->toBeFalse();
});

it('tracks quiz attempts and auto-saved answers', function () {
    $attempt = UserQuizAttempt::factory()->create();
    $question = Question::factory()->for($attempt->quiz)->create();

    // Simulasi auto-save: upsert aman berkat unique(attempt_id, question_id)
    UserQuizAnswer::upsert(
        [['attempt_id' => $attempt->id, 'question_id' => $question->id, 'selected_option' => 'B', 'is_doubtful' => true]],
        uniqueBy: ['attempt_id', 'question_id'],
        update: ['selected_option', 'is_doubtful'],
    );

    expect($attempt->answers()->count())->toBe(1)
        ->and($attempt->answers->first()->selected_option)->toBe('B')
        ->and($attempt->answers->first()->is_correct)->toBeNull();
});
