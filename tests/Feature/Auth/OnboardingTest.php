<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\User\Onboarding;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

// ==========================================
// PENGARAHAN KE ONBOARDING
// ==========================================

test('a new user is redirected from the dashboard to onboarding', function () {
    $user = User::factory()->notOnboarded()->create();

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertRedirect(route('user.onboarding'));
});

test('an onboarded user visiting onboarding is sent back to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user.onboarding'))
        ->assertRedirect(route('user.dashboard'));
});

test('registration redirects towards onboarding', function () {
    Livewire::test(Register::class)
        ->set('name', 'Pejuang Baru')
        ->set('email', 'baru@example.com')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('register')
        ->assertRedirect(route('user.onboarding'));

    expect(User::where('email', 'baru@example.com')->sole()->onboarded_at)->toBeNull();
});

test('email verification sends a new user to onboarding', function () {
    $user = User::factory()->unverified()->notOnboarded()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('user.onboarding'));
});

test('email verification sends an onboarded user to the dashboard', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('user.dashboard').'?verified=1');
});

test('login routes a not-yet-onboarded user to onboarding and others to the dashboard', function () {
    $fresh = User::factory()->notOnboarded()->create(['password' => 'password']);
    $veteran = User::factory()->create(['password' => 'password']);

    Livewire::test(Login::class)
        ->set('email', $fresh->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('user.onboarding'));

    auth()->logout();

    Livewire::test(Login::class)
        ->set('email', $veteran->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('user.dashboard'));
});

// ==========================================
// AKSI DI HALAMAN ONBOARDING
// ==========================================

test('finishing (or skipping) onboarding marks the user and goes to the dashboard', function () {
    $user = User::factory()->notOnboarded()->create();

    Livewire::actingAs($user)
        ->test(Onboarding::class)
        ->call('finish')
        ->assertRedirect(route('user.dashboard'));

    expect($user->refresh()->hasOnboarded())->toBeTrue();
});

test('choosing a package from onboarding marks the user onboarded and opens checkout', function () {
    $user = User::factory()->notOnboarded()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    Livewire::actingAs($user)
        ->test(Onboarding::class)
        ->call('checkout', $plan->id)
        ->assertRedirect(route('user.checkout', $plan));

    expect($user->refresh()->hasOnboarded())->toBeTrue();
});
