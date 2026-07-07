<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        // Statistik utama platform
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'active_packages' => Package::active()->count(),
            'total_quizzes' => Quiz::count(),
            // Pendapatan = total pembayaran yang sudah settled
            'revenue' => Payment::where('status', PaymentStatus::Settled)->sum('amount'),
        ];

        // User terbaru untuk tabel ringkas
        $recentUsers = User::latest()->take(5)->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
        ]);
    }
}
