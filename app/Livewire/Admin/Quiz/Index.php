<?php

namespace App\Livewire\Admin\Quiz;

use App\Actions\Quiz\CreateQuiz;
use App\Actions\Quiz\DeleteQuiz;
use App\Actions\Quiz\UpdateQuiz;
use App\DTOs\Quiz\QuizData;
use App\Models\Quiz;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Kelola Kuis')]
class Index extends Component
{
    use Toast, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    // --- STATE FORM (modal create/edit) ---
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public int $durationMinutes = 100;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'durationMinutes' => 'required|integer|min:1|max:600',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'judul kuis',
            'durationMinutes' => 'durasi',
        ];
    }

    // --- AKSI MODAL ---

    public function openCreate(): void
    {
        $this->authorize('create', Quiz::class);

        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(Quiz $quiz): void
    {
        $this->authorize('update', $quiz);

        $this->resetForm();
        $this->editingId = $quiz->id;
        $this->title = $quiz->title;
        $this->description = $quiz->description ?? '';
        $this->durationMinutes = $quiz->duration_minutes;

        $this->showForm = true;
    }

    // --- PERSISTENSI ---

    public function save(CreateQuiz $create, UpdateQuiz $update): void
    {
        $this->validate();

        // Bungkus input form ke DTO
        $data = new QuizData(
            title: $this->title,
            description: $this->description ?: null,
            durationMinutes: $this->durationMinutes,
        );

        if ($this->editingId) {
            $quiz = Quiz::findOrFail($this->editingId);
            $this->authorize('update', $quiz);
            $update->execute($quiz, $data);
            $this->success('Kuis diperbarui.', position: 'toast-top');
        } else {
            $this->authorize('create', Quiz::class);
            $create->execute($data);
            $this->success('Kuis berhasil dibuat.', position: 'toast-top');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Quiz $quiz, DeleteQuiz $action): void
    {
        $this->authorize('delete', $quiz);

        $action->execute($quiz);
        $this->success('Kuis dihapus.', position: 'toast-top');
    }

    // --- HELPERS ---

    private function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'durationMinutes']);
        $this->resetValidation();
    }

    public function render()
    {
        $quizzes = Quiz::query()
            ->withCount('questions')
            ->with('roadmapItems.module.package:id,name')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.quiz.index', [
            'quizzes' => $quizzes,
        ]);
    }
}
