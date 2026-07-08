<?php

namespace App\Livewire\Exam;

use App\Actions\Quiz\CheckQuizAccess;
use App\Actions\Quiz\StartQuizAttempt;
use App\Models\Package;
use App\Models\Quiz;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Persiapan Ujian')]
class QuizBriefing extends Component
{
    public Quiz $quiz;

    public function mount(Quiz $quiz, CheckQuizAccess $access): void
    {
        // Gerbang yang sama dengan ruang ujian — halaman persiapan pun tak boleh bocor
        abort_unless($access->subscribed(auth()->user(), $quiz), 403, 'Kamu belum berlangganan paket yang memuat try out ini.');
        abort_unless($access->unlocked(auth()->user(), $quiz), 403, 'Selesaikan materi sebelumnya untuk membuka try out ini.');

        $this->quiz = $quiz->loadCount('questions');
    }

    /** Sesi berjalan (jika ada): CTA berubah jadi "Lanjutkan Ujian" tanpa mereset timer */
    #[Computed]
    public function inProgressAttempt()
    {
        return auth()->user()->quizAttempts()
            ->inProgress()
            ->where('quiz_id', $this->quiz->id)
            ->latest('started_at')
            ->first();
    }

    /** Ringkasan struktur ujian: total poin + komposisi soal per section */
    #[Computed]
    public function stats(): array
    {
        return [
            'total_points' => (int) $this->quiz->questions()->sum('points'),
            'sections' => $this->quiz->questions()
                ->selectRaw('section, count(*) as total')
                ->groupBy('section')
                ->orderBy('section')
                ->pluck('total', 'section'),
        ];
    }

    /** Paket asal untuk tautan kembali ke roadmap (kuis bisa dipakai lintas paket) */
    #[Computed]
    public function backPackage(): ?Package
    {
        return $this->quiz->roadmapItems()->with('module.package')->first()?->module->package;
    }

    /** Timer baru berjalan di sini — bukan saat halaman persiapan dibuka */
    public function start(StartQuizAttempt $start): void
    {
        $start->execute(auth()->user(), $this->quiz);

        $this->redirectRoute('user.exam', $this->quiz);
    }

    public function render()
    {
        return view('livewire.exam.quiz-briefing');
    }
}
