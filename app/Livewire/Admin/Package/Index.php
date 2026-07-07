<?php

namespace App\Livewire\Admin\Package;

use App\Actions\Package\CreatePackage;
use App\Actions\Package\DeletePackage;
use App\Actions\Package\UpdatePackage;
use App\DTOs\Package\PackageData;
use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Kelola Paket')]
class Index extends Component
{
    use Toast, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    // --- STATE FORM (modal create/edit) ---
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $isActive = true;

    /** @var array<int, array{id: int|null, name: string, duration_days: int|string, price: int|string}> */
    public array $plans = [];

    /**
     * Aturan validasi form paket + baris plan bersarang.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
            'plans' => 'required|array|min:1',
            'plans.*.name' => 'required|string|max:255',
            'plans.*.duration_days' => 'required|integer|min:1',
            'plans.*.price' => 'required|numeric|min:0',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama paket',
            'plans.*.name' => 'nama plan',
            'plans.*.duration_days' => 'durasi',
            'plans.*.price' => 'harga',
        ];
    }

    // --- AKSI MODAL ---

    public function openCreate(): void
    {
        $this->authorize('create', Package::class);

        $this->resetForm();
        $this->plans = [$this->blankPlan()];
        $this->showForm = true;
    }

    public function openEdit(Package $package): void
    {
        $this->authorize('update', $package);

        $this->resetForm();
        $this->editingId = $package->id;
        $this->name = $package->name;
        $this->description = $package->description ?? '';
        $this->isActive = $package->is_active;
        $this->plans = $package->plans
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'duration_days' => $plan->duration_days,
                'price' => (int) $plan->price, // Tampilkan bulat di form (rupiah tanpa sen)
            ])
            ->all();

        $this->showForm = true;
    }

    public function addPlan(): void
    {
        $this->plans[] = $this->blankPlan();
    }

    public function removePlan(int $index): void
    {
        unset($this->plans[$index]);
        $this->plans = array_values($this->plans); // Re-index agar binding wire:model tetap rapi
    }

    // --- PERSISTENSI ---

    public function save(CreatePackage $create, UpdatePackage $update): void
    {
        $this->validate();

        // Bungkus input form ke DTO
        $data = new PackageData(
            name: $this->name,
            description: $this->description ?: null,
            isActive: $this->isActive,
            plans: $this->plans,
        );

        if ($this->editingId) {
            $package = Package::findOrFail($this->editingId);
            $this->authorize('update', $package);
            $update->execute($package, $data);
            $this->success('Paket diperbarui.', position: 'toast-top');
        } else {
            $this->authorize('create', Package::class);
            $create->execute($data);
            $this->success('Paket berhasil dibuat.', position: 'toast-top');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    /** Toggle cepat status aktif langsung dari tabel */
    public function toggleActive(Package $package): void
    {
        $this->authorize('update', $package);

        $package->update(['is_active' => ! $package->is_active]);
    }

    public function delete(Package $package, DeletePackage $action): void
    {
        $this->authorize('delete', $package);

        $action->execute($package);
        $this->success('Paket dihapus.', position: 'toast-top');
    }

    // --- HELPERS ---

    private function blankPlan(): array
    {
        return ['id' => null, 'name' => '', 'duration_days' => 30, 'price' => ''];
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description', 'isActive', 'plans']);
        $this->resetValidation();
    }

    public function render()
    {
        $packages = Package::query()
            ->withCount(['plans', 'quizzes', 'subscriptions'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.package.index', [
            'packages' => $packages,
        ]);
    }
}
