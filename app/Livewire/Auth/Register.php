<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.guest')]
#[Title('Register')]
class Register extends Component
{
    use Toast;

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|min:6|confirmed')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation = '';

    // Kode referral pengundang, ter-capture otomatis dari URL ?ref=CETARXXXXX
    #[Url(as: 'ref')]
    public ?string $referralCode = null;

    public function register(RegisterUserAction $action)
    {
        $this->validate();

        // Bungkus data ke DTO
        $data = new RegisterData(
            name: $this->name,
            email: $this->email,
            password: $this->password,
            referralCode: $this->referralCode,
        );

        // Eksekusi Action (buat user + referral code + auto login + kirim email verifikasi)
        $action->execute($data);

        session()->flash('success', 'Akun berhasil dibuat! Silakan verifikasi email kamu.');

        // Tujuan akhir: onboarding (middleware `verified` mengarahkan ke halaman verifikasi dulu)
        return redirect()->route('user.onboarding');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
