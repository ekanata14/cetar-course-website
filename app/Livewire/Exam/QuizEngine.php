<?php

namespace App\Livewire\Exam;

use App\Actions\Quiz\SaveUserAnswer;
use App\Actions\Quiz\StartQuizAttempt;
use App\Actions\Quiz\SubmitQuizAttempt;
use App\Enums\AttemptStatus;
use App\Models\Quiz;
use App\Models\UserQuizAttempt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Ujian')]
class QuizEngine extends Component
{
    public Quiz $quiz;

    public UserQuizAttempt $attempt;

    /** Index soal yang sedang ditampilkan (navigasi one-question-at-a-time) */
    public int $currentIndex = 0;

    /**
     * Peta jawaban lokal untuk render instan grid & opsi.
     * Bentuk: [question_id => ['selected' => 'A'|null, 'doubtful' => bool]]
     *
     * @var array<int, array{selected: ?string, doubtful: bool}>
     */
    public array $answers = [];

    public function mount(Quiz $quiz, StartQuizAttempt $start, SubmitQuizAttempt $submit): void
    {
        // GATE AKSES: user harus punya langganan aktif pada salah satu paket yang memuat kuis ini
        abort_unless($this->userHasAccess($quiz), 403, 'Kamu belum berlangganan paket yang memuat try out ini.');

        $this->quiz = $quiz;

        // Mulai baru atau lanjutkan attempt in_progress (started_at TIDAK direset saat refresh)
        $this->attempt = $start->execute(auth()->user(), $quiz);

        // Guard server: kalau waktu sudah habis saat halaman dibuka, langsung finalisasi & ke hasil
        if ($this->attempt->isExpired()) {
            $submit->execute($this->attempt);
            $this->redirectRoute('user.exam.result', $this->attempt);

            return;
        }

        // Hidrasi jawaban tersimpan (resume ujian menampilkan pilihan sebelumnya)
        $this->answers = $this->attempt->answers()
            ->get(['question_id', 'selected_option', 'is_doubtful'])
            ->mapWithKeys(fn ($answer) => [
                $answer->question_id => [
                    'selected' => $answer->selected_option,
                    'doubtful' => $answer->is_doubtful,
                ],
            ])
            ->all();
    }

    /**
     * Payload soal untuk sesi ujian.
     * PENTING: correct_answer & explanation sengaja TIDAK di-select agar
     * kunci jawaban tidak pernah bocor ke browser selama ujian berlangsung.
     */
    #[Computed]
    public function questions()
    {
        return $this->quiz->questions()
            ->orderBy('section')
            ->orderBy('id')
            ->get(['id', 'section', 'passage', 'text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'points']);
    }

    #[Computed]
    public function currentQuestion()
    {
        return $this->questions[$this->currentIndex] ?? null;
    }

    /** Deadline absolut dalam epoch milidetik — dikonsumsi countdown timer Alpine */
    #[Computed]
    public function deadlineMs(): int
    {
        return $this->attempt->deadline()->getTimestampMs();
    }

    // --- INTERAKSI UJIAN ---

    /** Pilih opsi jawaban: simpan ke server (auto-save) + update peta lokal */
    public function selectAnswer(int $questionId, string $option, SaveUserAnswer $save): void
    {
        if (! in_array($option, ['A', 'B', 'C', 'D', 'E'], true)) {
            return;
        }

        $doubtful = $this->answers[$questionId]['doubtful'] ?? false;

        if ($save->execute($this->attempt, $questionId, $option, $doubtful)) {
            $this->answers[$questionId] = ['selected' => $option, 'doubtful' => $doubtful];
        }
    }

    /** Toggle status "Ragu-ragu" pada soal aktif */
    public function toggleDoubt(int $questionId, SaveUserAnswer $save): void
    {
        $current = $this->answers[$questionId] ?? ['selected' => null, 'doubtful' => false];
        $newDoubtful = ! $current['doubtful'];

        if ($save->execute($this->attempt, $questionId, $current['selected'], $newDoubtful)) {
            $this->answers[$questionId] = ['selected' => $current['selected'], 'doubtful' => $newDoubtful];
        }
    }

    // --- NAVIGASI (state server agar konsisten saat re-render) ---

    public function goTo(int $index): void
    {
        if ($index >= 0 && $index < $this->questions->count()) {
            $this->currentIndex = $index;
        }
    }

    public function next(): void
    {
        $this->goTo($this->currentIndex + 1);
    }

    public function previous(): void
    {
        $this->goTo($this->currentIndex - 1);
    }

    // --- FINALISASI (submit manual ATAU auto-submit dari timer Alpine) ---

    public function submitQuiz(SubmitQuizAttempt $submit): void
    {
        // Idempotent: aman dipanggil ganda (klik submit + timer habis bersamaan)
        if ($this->attempt->status !== AttemptStatus::Completed) {
            $submit->execute($this->attempt);
        }

        $this->redirectRoute('user.exam.result', $this->attempt);
    }

    // --- HELPERS ---

    private function userHasAccess(Quiz $quiz): bool
    {
        return auth()->user()
            ->subscriptions()
            ->active()
            ->whereIn('package_id', $quiz->packages()->pluck('packages.id'))
            ->exists();
    }

    public function render()
    {
        return view('livewire.exam.quiz-engine');
    }
}
