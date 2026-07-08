<?php

namespace App\Livewire\Admin\Quiz;

use App\Actions\Quiz\DeleteQuestion;
use App\Actions\Quiz\ImportQuestions;
use App\Actions\Quiz\SaveQuestion;
use App\DTOs\Quiz\QuestionData;
use App\Models\Question;
use App\Models\Quiz;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Kelola Soal')]
class Questions extends Component
{
    use Toast;
    use WithFileUploads;

    public Quiz $quiz;

    /** Filter section aktif ('' = semua) */
    public string $activeSection = '';

    // --- STATE FORM (modal create/edit soal) ---
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $section = '';

    public string $passage = '';

    public string $text = '';

    public string $optionA = '';

    public string $optionB = '';

    public string $optionC = '';

    public string $optionD = '';

    public string $optionE = '';

    public string $correctAnswer = 'A';

    public int $points = 5;

    public string $explanation = '';

    public string $imageUrl = '';

    // --- STATE IMPOR EXCEL ---
    public bool $showImport = false;

    public $importFile = null;

    /** @var array<int, string> Error per baris dari impor terakhir */
    public array $importErrors = [];

    public function mount(Quiz $quiz): void
    {
        $this->authorize('update', $quiz);

        $this->quiz = $quiz;
    }

    protected function rules(): array
    {
        return [
            'section' => 'nullable|string|max:50',
            'passage' => 'nullable|string',
            'text' => 'required|string',
            'optionA' => 'required|string',
            'optionB' => 'required|string',
            'optionC' => 'required|string',
            'optionD' => 'required|string',
            'optionE' => 'nullable|string',
            // Opsi E kosong -> jawaban benar tidak boleh E
            'correctAnswer' => $this->optionE === '' ? 'required|in:A,B,C,D' : 'required|in:A,B,C,D,E',
            'points' => 'required|integer|min:0|max:100',
            'explanation' => 'nullable|string',
            'imageUrl' => 'nullable|url|max:2048',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'text' => 'pertanyaan',
            'optionA' => 'opsi A',
            'optionB' => 'opsi B',
            'optionC' => 'opsi C',
            'optionD' => 'opsi D',
            'correctAnswer' => 'jawaban benar',
            'points' => 'poin',
        ];
    }

    /** Daftar section unik pada kuis ini (untuk tab filter) */
    #[Computed]
    public function sections()
    {
        return $this->quiz->questions()
            ->whereNotNull('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section');
    }

    /** Soal sesuai filter section aktif */
    #[Computed]
    public function questions()
    {
        return $this->quiz->questions()
            ->when($this->activeSection !== '', fn ($q) => $q->where('section', $this->activeSection))
            ->orderBy('section')
            ->orderBy('id')
            ->get();
    }

    // --- AKSI MODAL ---

    public function openCreate(): void
    {
        $this->resetForm();
        // Pre-fill section dari tab yang sedang aktif agar input lebih cepat
        $this->section = $this->activeSection;
        $this->showForm = true;
    }

    public function openEdit(Question $question): void
    {
        $this->resetForm();
        $this->editingId = $question->id;
        $this->section = $question->section ?? '';
        $this->passage = $question->passage ?? '';
        $this->text = $question->text;
        $this->optionA = $question->option_a;
        $this->optionB = $question->option_b;
        $this->optionC = $question->option_c;
        $this->optionD = $question->option_d;
        $this->optionE = $question->option_e ?? '';
        $this->correctAnswer = $question->correct_answer;
        $this->points = $question->points;
        $this->explanation = $question->explanation ?? '';
        $this->imageUrl = $question->image_url ?? '';

        $this->showForm = true;
    }

    // --- PERSISTENSI ---

    public function save(SaveQuestion $action): void
    {
        $this->authorize('update', $this->quiz);

        $this->validate();

        // Bungkus input form ke DTO
        $data = new QuestionData(
            section: $this->section ?: null,
            passage: $this->passage ?: null,
            text: $this->text,
            optionA: $this->optionA,
            optionB: $this->optionB,
            optionC: $this->optionC,
            optionD: $this->optionD,
            optionE: $this->optionE ?: null,
            correctAnswer: $this->correctAnswer,
            points: $this->points,
            explanation: $this->explanation ?: null,
            imageUrl: $this->imageUrl ?: null,
        );

        $question = $this->editingId
            ? Question::where('quiz_id', $this->quiz->id)->findOrFail($this->editingId)
            : null;

        $action->execute($this->quiz, $data, $question);

        $this->success($question ? 'Soal diperbarui.' : 'Soal ditambahkan.', position: 'toast-top');
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Question $question, DeleteQuestion $action): void
    {
        $this->authorize('update', $this->quiz);

        // Pastikan soal memang milik kuis ini
        abort_unless($question->quiz_id === $this->quiz->id, 404);

        $action->execute($question);
        $this->success('Soal dihapus.', position: 'toast-top');
    }

    // --- IMPOR EXCEL ---

    public function openImport(): void
    {
        $this->reset(['importFile', 'importErrors']);
        $this->resetValidation();
        $this->showImport = true;
    }

    public function import(ImportQuestions $action): void
    {
        $this->authorize('update', $this->quiz);

        $this->validate(
            ['importFile' => 'required|file|mimes:xlsx,csv|max:10240'],
            [],
            ['importFile' => 'file Excel'],
        );

        $result = $action->execute($this->quiz, $this->importFile->getRealPath());

        if ($result->failed()) {
            $this->importErrors = $result->errors;
            $this->error('Impor dibatalkan — perbaiki file lalu unggah ulang.', position: 'toast-top');

            return;
        }

        $this->success("{$result->imported} soal berhasil diimpor.", position: 'toast-top');
        $this->showImport = false;
        $this->reset(['importFile', 'importErrors']);
    }

    // --- HELPERS ---

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'section', 'passage', 'text',
            'optionA', 'optionB', 'optionC', 'optionD', 'optionE',
            'correctAnswer', 'points', 'explanation', 'imageUrl',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.quiz.questions');
    }
}
