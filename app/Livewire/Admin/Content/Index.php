<?php

namespace App\Livewire\Admin\Content;

use App\Actions\Content\CreateContent;
use App\Actions\Content\DeleteContent;
use App\Actions\Content\UpdateContent;
use App\DTOs\Content\ContentData;
use App\Models\Content;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Kelola Materi')]
class Index extends Component
{
    use Toast, WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    // --- STATE FORM (modal create/edit) ---
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $type = 'text';

    public string $body = '';

    public string $videoUrl = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $pdfFile = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'type' => 'required|in:text,pdf,video',
            'body' => 'required_if:type,text|nullable|string',
            'videoUrl' => 'required_if:type,video|nullable|url',
            // PDF wajib saat membuat materi baru bertipe pdf; opsional saat edit (pertahankan file lama)
            'pdfFile' => ($this->type === 'pdf' && ! $this->editingId ? 'required|' : 'nullable|').'file|mimes:pdf|max:20480',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'title' => 'judul materi',
            'body' => 'isi materi',
            'videoUrl' => 'URL video',
            'pdfFile' => 'file PDF',
        ];
    }

    // --- AKSI MODAL ---

    public function openCreate(): void
    {
        $this->authorize('create', Content::class);

        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(Content $content): void
    {
        $this->authorize('update', $content);

        $this->resetForm();
        $this->editingId = $content->id;
        $this->title = $content->title;
        $this->type = $content->type->value;
        $this->body = $content->body ?? '';
        $this->videoUrl = $content->video_url ?? '';

        $this->showForm = true;
    }

    // --- PERSISTENSI ---

    public function save(CreateContent $create, UpdateContent $update): void
    {
        $this->validate();

        // Simpan upload PDF (jika ada) lalu bungkus ke DTO
        $filePath = $this->pdfFile?->store('contents', 'public');

        $data = new ContentData(
            title: $this->title,
            type: $this->type,
            body: $this->body ?: null,
            filePath: $filePath,
            videoUrl: $this->videoUrl ?: null,
        );

        if ($this->editingId) {
            $content = Content::findOrFail($this->editingId);
            $this->authorize('update', $content);
            $update->execute($content, $data);
            $this->success('Materi diperbarui.', position: 'toast-top');
        } else {
            $this->authorize('create', Content::class);
            $create->execute($data);
            $this->success('Materi berhasil dibuat.', position: 'toast-top');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Content $content, DeleteContent $action): void
    {
        $this->authorize('delete', $content);

        $action->execute($content);
        $this->success('Materi dihapus.', position: 'toast-top');
    }

    // --- HELPERS ---

    private function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'type', 'body', 'videoUrl', 'pdfFile']);
        $this->resetValidation();
    }

    public function render()
    {
        $contents = Content::query()
            ->with('roadmapItems.module.package:id,name')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.content.index', [
            'contents' => $contents,
        ]);
    }
}
