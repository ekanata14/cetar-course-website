<?php

namespace App\Livewire\User;

use App\Actions\Roadmap\MarkItemComplete;
use App\Actions\Roadmap\ResolveJourney;
use App\Models\Package;
use App\Models\UserQuizAttempt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.guest')]
#[Title('Ruang Belajar')]
class MaterialsJourney extends Component
{
    use Toast;

    public Package $package;

    /** Item roadmap yang sedang dibuka di panel kanan (deep-link via ?item=) */
    #[Url(as: 'item')]
    public ?int $activeItemId = null;

    public function mount(Package $package): void
    {
        // GATE: hanya pelanggan aktif paket ini yang boleh masuk ruang belajar
        abort_unless(
            auth()->user()->hasActiveSubscription($package),
            403,
            'Kamu belum berlangganan paket ini.',
        );

        $this->package = $package;

        // Validasi deep-link ?item= (harus milik paket ini & sudah terbuka);
        // default: item pertama yang terbuka dan belum selesai
        $requested = $this->activeItemId ? $this->findItem($this->activeItemId) : null;

        $this->activeItemId = ($requested && $requested->is_unlocked)
            ? $requested->id
            : $this->defaultItemId();
    }

    /** Modul + item terdekorasi is_completed / is_unlocked / progress_percent */
    #[Computed]
    public function modules()
    {
        return app(ResolveJourney::class)->execute(auth()->user(), $this->package);
    }

    /** Item aktif (terdekorasi) untuk panel kanan */
    #[Computed]
    public function activeItem()
    {
        return $this->activeItemId ? $this->findItem($this->activeItemId) : null;
    }

    /** Item try out (terdekorasi) untuk tab Try Out di sidebar */
    #[Computed]
    public function quizItems()
    {
        return $this->modules
            ->flatMap(fn ($module) => $module->items)
            ->filter(fn ($item) => $item->isQuiz())
            ->values();
    }

    /** Riwayat pengerjaan user per kuis (terbaru dulu), untuk tab Try Out */
    #[Computed]
    public function attemptsByQuiz()
    {
        $quizIds = $this->quizItems->pluck('contentable_id');

        return UserQuizAttempt::where('user_id', auth()->id())
            ->whereIn('quiz_id', $quizIds)
            ->latest('started_at')
            ->get()
            ->groupBy('quiz_id');
    }

    /** Progres keseluruhan paket untuk header sidebar */
    #[Computed]
    public function overall(): array
    {
        $items = $this->modules->flatMap(fn ($module) => $module->items);

        $total = $items->count();
        $completed = $items->where('is_completed', true)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'percent' => $total > 0 ? (int) round($completed / $total * 100) : 0,
        ];
    }

    /** Pindah item dari sidebar — item terkunci diabaikan (gerbang tetap di server) */
    public function selectItem(int $itemId): void
    {
        $item = $this->findItem($itemId);

        if (! $item || ! $item->is_unlocked) {
            return;
        }

        $this->activeItemId = $item->id;
    }

    /** Tandai materi aktif selesai lalu lanjut otomatis ke item berikutnya */
    public function markAsComplete(MarkItemComplete $action): void
    {
        $item = $this->activeItem;

        if (! $item || $item->contentable_type !== 'content' || $item->is_completed) {
            return;
        }

        $action->execute(auth()->user(), $item);

        unset($this->modules, $this->activeItem, $this->overall, $this->prevItemId, $this->nextItemId);

        $this->success('Materi selesai! Item berikutnya terbuka.', position: 'toast-top');

        // Auto-lanjut: item pertama yang kini terbuka dan belum selesai
        if ($next = $this->defaultItemId(skipCompleted: true)) {
            $this->activeItemId = $next;
        }
    }

    /** Id item sebelum item aktif dalam urutan flat (tanpa gerbang kunci — selalu boleh mundur) */
    #[Computed]
    public function prevItemId(): ?int
    {
        $items = $this->modules->flatMap(fn ($module) => $module->items)->values();
        $index = $items->search(fn ($item) => $item->id === $this->activeItemId);

        return ($index !== false && $index > 0) ? $items[$index - 1]->id : null;
    }

    /** Id item sesudah item aktif — hanya dikembalikan jika item berikutnya sudah terbuka */
    #[Computed]
    public function nextItemId(): ?int
    {
        $items = $this->modules->flatMap(fn ($module) => $module->items)->values();
        $index = $items->search(fn ($item) => $item->id === $this->activeItemId);

        if ($index === false || $index >= $items->count() - 1) {
            return null;
        }

        $next = $items[$index + 1];

        return $next->is_unlocked ? $next->id : null;
    }

    /** Navigasi mundur — tidak memerlukan gerbang kunci */
    public function goToPrev(): void
    {
        if ($id = $this->prevItemId) {
            $this->activeItemId = $id;
        }
    }

    /** Navigasi maju — hanya jika item berikutnya terbuka */
    public function goToNext(): void
    {
        if ($id = $this->nextItemId) {
            $this->selectItem($id);
        }
    }

    // --- HELPERS ---

    /** Cari item terdekorasi di roadmap paket ini */
    private function findItem(int $itemId)
    {
        return $this->modules
            ->flatMap(fn ($module) => $module->items)
            ->firstWhere('id', $itemId);
    }

    /** Item default: pertama yang terbuka & belum selesai; fallback item pertama */
    private function defaultItemId(bool $skipCompleted = false): ?int
    {
        $items = $this->modules->flatMap(fn ($module) => $module->items);

        $candidate = $items->first(fn ($item) => $item->is_unlocked && ! $item->is_completed)
            ?? ($skipCompleted ? null : $items->first());

        return $candidate?->id;
    }

    public function render()
    {
        return view('livewire.user.materials-journey');
    }
}
