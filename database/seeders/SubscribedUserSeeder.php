<?php

namespace Database\Seeders;

use App\Enums\AttemptStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizAttempt;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SubscribedUserSeeder extends Seeder
{
    /**
     * Demo learner account dengan langganan aktif di semua paket dan satu percobaan try out selesai.
     * Login: demo@cetar.id / password
     */
    public function run(): void
    {
        $demo = User::firstOrCreate(
            ['email' => 'demo@cetar.id'],
            [
                'name'              => 'Demo Pejuang',
                'password'          => Hash::make('password'),
                'role'              => 'user',
                'email_verified_at' => now(),
                'onboarded_at'      => now(),
                'referral_code'     => 'CETARDEMO',
            ]
        );

        $packages = Package::active()->with('plans')->get();

        foreach ($packages as $package) {
            // Ambil plan termurah (durasi terpendek)
            $plan = $package->plans->sortBy('duration_days')->first();

            if (! $plan) {
                continue;
            }

            // Catat pembayaran yang sudah lunas
            Payment::firstOrCreate(
                ['external_id' => 'CETAR-INV-DEMO-' . strtoupper($package->slug)],
                [
                    'user_id'         => $demo->id,
                    'package_plan_id' => $plan->id,
                    'amount'          => $plan->price,
                    'status'          => PaymentStatus::Settled,
                ]
            );

            // Buat atau perbarui langganan aktif
            UserSubscription::updateOrCreate(
                ['user_id' => $demo->id, 'package_id' => $package->id],
                ['status' => SubscriptionStatus::Active, 'expires_at' => now()->addDays(30)]
            );
        }

        // -------------------------------------------------------
        // Percobaan try out selesai (untuk preview halaman hasil)
        // -------------------------------------------------------
        $firstPackage = $packages->first();
        $quiz = $firstPackage?->quizzes()->with('questions')->first();

        $attempt = null;

        if ($quiz && $quiz->questions->isNotEmpty()) {
            $attempt = UserQuizAttempt::create([
                'user_id'      => $demo->id,
                'quiz_id'      => $quiz->id,
                'status'       => AttemptStatus::Completed,
                'started_at'   => now()->subHour(),
                'completed_at' => now()->subMinutes(30),
                'score'        => 0,
            ]);

            $totalScore = 0;

            foreach ($quiz->questions as $index => $question) {
                // Jawab benar ~67% soal (setiap soal ke-3 dijawab salah)
                $answerCorrectly = ($index % 3) !== 2;

                if ($answerCorrectly) {
                    $selected  = $question->correct_answer;
                    $isCorrect = true;
                    $totalScore += $question->points;
                } else {
                    // Pilih opsi salah (a jika kunci bukan a, sebaliknya b)
                    $selected  = $question->correct_answer !== 'a' ? 'a' : 'b';
                    $isCorrect = false;
                }

                UserQuizAnswer::create([
                    'attempt_id'      => $attempt->id,
                    'question_id'     => $question->id,
                    'selected_option' => $selected,
                    'is_correct'      => $isCorrect,
                    'is_doubtful'     => false,
                ]);
            }

            $attempt->update(['score' => $totalScore]);
        }

        $this->command->info('Login Demo User: demo@cetar.id / password');
        $this->command->info('  → Langganan aktif: '.$packages->count().' paket');

        if ($attempt) {
            $this->command->info("  → Try out selesai: {$quiz->title} (skor {$attempt->score})");
            $this->command->info("  → Lihat hasil: /user/exam/result/{$attempt->id}");
        }
    }
}
