<?php

namespace App\Livewire\Exam;

use App\Actions\Quiz\CheckQuizAccess;
use App\Actions\Quiz\SaveUserAnswer;
use App\Actions\Quiz\StartQuizAttempt;
use App\Actions\Quiz\SubmitQuizAttempt;
use App\Enums\AttemptStatus;
use App\Livewire\Exam\Concerns\HasSectionOrdering;
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
    use HasSectionOrdering;

    public Quiz $quiz;

    public UserQuizAttempt $attempt;

    /** Section (tab) yang sedang aktif — mis. 'TWK'. Semua soal tetap satu sesi. */
    public string $activeSection = '';

    /** Index soal dalam section aktif (navigasi one-question-at-a-time per section) */
    public int $currentIndex = 0;

    /**
     * Peta jawaban lokal untuk render instan grid & opsi.
     * Bentuk: [question_id => ['selected' => 'A'|null, 'doubtful' => bool]]
     *
     * @var array<int, array{selected: ?string, doubtful: bool}>
     */
    public array $answers = [];

    public function mount(Quiz $quiz, StartQuizAttempt $start, SubmitQuizAttempt $submit, CheckQuizAccess $access): void
    {
        // GATE AKSES 1: user harus punya langganan aktif pada salah satu paket yang memuat kuis ini
        abort_unless($access->subscribed(auth()->user(), $quiz), 403, 'Kamu belum berlangganan paket yang memuat try out ini.');

        // GATE AKSES 2: item roadmap kuis ini harus sudah terbuka (materi sebelumnya selesai)
        abort_unless($access->unlocked(auth()->user(), $quiz), 403, 'Selesaikan materi sebelumnya untuk membuka try out ini.');

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

        // Tab awal: section pertama sesuai urutan baku (TWK → TIU → TKP → …)
        $this->activeSection = $this->sections->first() ?? '';
    }

    /**
     * Payload soal untuk sesi ujian, diurutkan section baku lalu id.
     * PENTING: correct_answer & explanation sengaja TIDAK di-select agar
     * kunci jawaban tidak pernah bocor ke browser selama ujian berlangsung.
     */
    #[Computed]
    public function questions()
    {
        $questions = $this->quiz->questions()
            ->get(['id', 'section', 'passage', 'image_url', 'text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'points']);

        // Posisi tiap section (TWK=0, TIU=1, …) untuk pengurutan global
        $position = $this->orderSections($questions->map(fn ($q) => $this->sectionKey($q->section)))->flip();

        return $questions
            ->sortBy(fn ($q) => [$position[$this->sectionKey($q->section)], $q->id])
            ->values();
    }

    /** Daftar kunci section yang tersedia pada kuis ini, terurut baku (drive tab). */
    #[Computed]
    public function sections()
    {
        return $this->orderSections($this->questions->map(fn ($q) => $this->sectionKey($q->section)));
    }

    /** Soal pada section aktif saja — menyetir kartu, grid, penomoran & counter. */
    #[Computed]
    public function sectionQuestions()
    {
        return $this->questions
            ->filter(fn ($q) => $this->sectionKey($q->section) === $this->activeSection)
            ->values();
    }

    #[Computed]
    public function currentQuestion()
    {
        return $this->sectionQuestions[$this->currentIndex] ?? null;
    }

    /** Jumlah soal terjawab pada section aktif (untuk counter sidebar). */
    #[Computed]
    public function sectionAnsweredCount(): int
    {
        return $this->sectionQuestions
            ->filter(fn ($q) => ($this->answers[$q->id]['selected'] ?? null) !== null)
            ->count();
    }

    /** Jumlah soal terjawab di seluruh sesi (untuk ringkasan sebelum submit). */
    #[Computed]
    public function answeredCount(): int
    {
        return collect($this->answers)->filter(fn ($a) => $a['selected'] !== null)->count();
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

    /** Pindah tab section; reset ke soal pertama section tersebut. */
    public function setSection(string $section): void
    {
        if ($this->sections->contains($section)) {
            $this->activeSection = $section;
            $this->currentIndex = 0;
        }
    }

    public function goTo(int $index): void
    {
        if ($index >= 0 && $index < $this->sectionQuestions->count()) {
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

    public function render()
    {
        return view('livewire.exam.quiz-engine');
    }
}
