<?php

namespace App\Livewire\Admin\Package;

use App\Actions\Roadmap\AddRoadmapItem;
use App\Actions\Roadmap\DeleteModule;
use App\Actions\Roadmap\RemoveRoadmapItem;
use App\Actions\Roadmap\ReorderItem;
use App\Actions\Roadmap\ReorderModule;
use App\Actions\Roadmap\SaveModule;
use App\Models\Content;
use App\Models\Package;
use App\Models\PackageModule;
use App\Models\Quiz;
use App\Models\RoadmapItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Roadmap Paket')]
class RoadmapBuilder extends Component
{
    use Toast;

    public Package $package;

    // --- STATE FORM MODUL ---
    public bool $showModuleForm = false;

    public ?int $editingModuleId = null;

    public string $moduleTitle = '';

    // --- STATE FORM TAMBAH ITEM ---
    public bool $showItemForm = false;

    public ?int $targetModuleId = null;

    public string $itemType = 'content'; // content | quiz

    public string $itemId = '';

    public bool $itemLocked = true;

    public function mount(Package $package): void
    {
        $this->authorize('update', $package);

        $this->package = $package;
    }

    /** Roadmap lengkap: modul → item → konten (eager, hindari N+1) */
    #[Computed]
    public function modules()
    {
        return $this->package->modules()->with('items.contentable')->get();
    }

    #[Computed]
    public function contentOptions()
    {
        return Content::orderBy('title')->get(['id', 'title', 'type']);
    }

    #[Computed]
    public function quizOptions()
    {
        return Quiz::orderBy('title')->get(['id', 'title']);
    }

    // --- MODUL ---

    public function openCreateModule(): void
    {
        $this->reset(['editingModuleId', 'moduleTitle']);
        $this->resetValidation();
        $this->showModuleForm = true;
    }

    public function openEditModule(PackageModule $module): void
    {
        $this->editingModuleId = $module->id;
        $this->moduleTitle = $module->title;
        $this->resetValidation();
        $this->showModuleForm = true;
    }

    public function saveModule(SaveModule $action): void
    {
        $this->validate(['moduleTitle' => 'required|string|min:3|max:255'], [], ['moduleTitle' => 'judul modul']);

        $action->execute($this->package, $this->editingModuleId, $this->moduleTitle);

        $this->success($this->editingModuleId ? 'Modul diperbarui.' : 'Modul ditambahkan.', position: 'toast-top');
        $this->showModuleForm = false;
        $this->reset(['editingModuleId', 'moduleTitle']);
        unset($this->modules);
    }

    public function deleteModule(PackageModule $module, DeleteModule $action): void
    {
        $action->execute($module);
        $this->success('Modul dihapus.', position: 'toast-top');
        unset($this->modules);
    }

    public function moveModule(PackageModule $module, string $direction, ReorderModule $action): void
    {
        $action->execute($module, $direction);
        unset($this->modules);
    }

    // --- ITEM ROADMAP ---

    public function openAddItem(PackageModule $module): void
    {
        $this->targetModuleId = $module->id;
        $this->reset(['itemType', 'itemId', 'itemLocked']);
        $this->resetValidation();
        $this->showItemForm = true;
    }

    public function addItem(AddRoadmapItem $action): void
    {
        $this->validate([
            'itemType' => 'required|in:content,quiz',
            'itemId' => 'required|integer|'.($this->itemType === 'quiz' ? 'exists:quizzes,id' : 'exists:contents,id'),
        ], [], ['itemId' => $this->itemType === 'quiz' ? 'kuis' : 'materi']);

        $module = $this->package->modules()->findOrFail($this->targetModuleId);

        $action->execute($module, $this->itemType, (int) $this->itemId, $this->itemLocked);

        $this->success('Item ditambahkan ke modul.', position: 'toast-top');
        $this->showItemForm = false;
        unset($this->modules);
    }

    public function removeItem(RoadmapItem $item, RemoveRoadmapItem $action): void
    {
        $action->execute($item);
        $this->success('Item dilepas dari modul.', position: 'toast-top');
        unset($this->modules);
    }

    public function moveItem(RoadmapItem $item, string $direction, ReorderItem $action): void
    {
        $action->execute($item, $direction);
        unset($this->modules);
    }

    /** Toggle kunci default langsung dari daftar */
    public function toggleLock(RoadmapItem $item): void
    {
        $item->update(['is_locked_by_default' => ! $item->is_locked_by_default]);
        unset($this->modules);
    }

    public function render()
    {
        return view('livewire.admin.package.roadmap-builder');
    }
}
