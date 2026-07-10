<?php

namespace App\Livewire\Exam;

use App\Enums\AttemptStatus;
use App\Livewire\Exam\Concerns\HasSectionOrdering;
use App\Models\UserQuizAttempt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Hasil Ujian')]
class QuizResult extends Component
{
    use HasSectionOrdering;

    public UserQuizAttempt $attempt;

    public function mount(UserQuizAttempt $attempt): void
    {
        // Hanya pemilik attempt yang boleh melihat hasilnya
        abort_unless($attempt->user_id === auth()->id(), 403);

        // Attempt yang masih berjalan tidak punya hasil — kembalikan ke ruang ujian
        if ($attempt->status !== AttemptStatus::Completed) {
            $this->redirectRoute('user.exam', $attempt->quiz_id);

            return;
        }

        $this->attempt = $attempt->load('quiz');
    }

    /**
     * Soal lengkap + jawaban user (keyed by question_id) untuk halaman review.
     * Kunci jawaban & pembahasan BOLEH tampil di sini — ujian sudah selesai.
     */
    #[Computed]
    public function questions()
    {
        $questions = $this->attempt->quiz->questions()
            ->orderBy('id')
            ->get();

        // Urutkan section baku (TWK → TIU → TKP → …) agar tab & review konsisten
        $position = $this->orderSections($questions->map(fn ($q) => $this->sectionKey($q->section)))->flip();

        return $questions
            ->sortBy(fn ($q) => [$position[$this->sectionKey($q->section)], $q->id])
            ->values();
    }

    /** Kunci section terurut baku — menyetir tab review di halaman hasil. */
    #[Computed]
    public function sections()
    {
        return $this->orderSections($this->questions->map(fn ($q) => $this->sectionKey($q->section)));
    }

    #[Computed]
    public function answersByQuestion()
    {
        return $this->attempt->answers()->get()->keyBy('question_id');
    }

    /** Ringkasan global: benar / salah / kosong + skor maksimal */
    #[Computed]
    public function summary(): array
    {
        $correct = 0;
        $wrong = 0;
        $blank = 0;
        $maxScore = 0;

        foreach ($this->questions as $question) {
            $maxScore += $question->points;
            $answer = $this->answersByQuestion->get($question->id);

            if (! $answer || $answer->selected_option === null) {
                $blank++;
            } elseif ($answer->is_correct) {
                $correct++;
            } else {
                $wrong++;
            }
        }

        return [
            'correct' => $correct,
            'wrong' => $wrong,
            'blank' => $blank,
            'max_score' => $maxScore,
            // Durasi pengerjaan riil (menit, dibulatkan ke atas)
            'duration_used' => (int) ceil($this->attempt->started_at->diffInSeconds($this->attempt->completed_at) / 60),
        ];
    }

    /** Rekap per section (TWK/TIU/TKP dst.): skor & jumlah benar */
    #[Computed]
    public function sectionStats()
    {
        return $this->questions
            ->groupBy(fn ($question) => $question->section ?? __('Umum'))
            ->map(function ($questions, $section) {
                $earned = 0;
                $correct = 0;

                foreach ($questions as $question) {
                    $answer = $this->answersByQuestion->get($question->id);

                    if ($answer?->is_correct) {
                        $earned += $question->points;
                        $correct++;
                    }
                }

                return [
                    'section' => $section,
                    'earned' => $earned,
                    'max' => $questions->sum('points'),
                    'correct' => $correct,
                    'total' => $questions->count(),
                ];
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.exam.quiz-result');
    }
}
