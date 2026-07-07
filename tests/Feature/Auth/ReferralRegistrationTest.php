<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

test('registration generates a unique referral code', function () {
    Livewire::test(Register::class)
        ->set('name', 'Budi Santoso')
        ->set('email', 'budi@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    $user = User::where('email', 'budi@example.com')->first();

    expect($user->referral_code)->toStartWith('CETAR')
        ->and($user->referred_by)->toBeNull()
        ->and($user->role)->toBe('user');
});

test('registration with a valid referral code links the referrer', function () {
    $referrer = User::factory()->create(['referral_code' => 'CETARABCDE']);

    Livewire::withQueryParams(['ref' => 'CETARABCDE'])
        ->test(Register::class)
        ->set('name', 'Ani Wijaya')
        ->set('email', 'ani@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    $user = User::where('email', 'ani@example.com')->first();

    expect($user->referred_by)->toBe($referrer->id);
});

test('an invalid referral code is silently ignored', function () {
    Livewire::withQueryParams(['ref' => 'KODENGAWUR'])
        ->test(Register::class)
        ->set('name', 'Citra Lestari')
        ->set('email', 'citra@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    expect(User::where('email', 'citra@example.com')->first()->referred_by)->toBeNull();
});
