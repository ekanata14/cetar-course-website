<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;

/**
 * Helper: profil Google palsu ala Socialite.
 */
function fakeGoogleUser(string $id = 'google-123', string $email = 'orang@gmail.com', string $name = 'Orang Google'): SocialiteUser
{
    return (new SocialiteUser)->map([
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'nickname' => null,
    ]);
}

test('a brand-new google account creates a verified user and lands on onboarding', function () {
    Socialite::shouldReceive('driver->user')->andReturn(fakeGoogleUser());

    $this->get('/auth/google/callback')->assertRedirect(route('user.onboarding'));

    $user = User::where('email', 'orang@gmail.com')->sole();

    expect($user->google_id)->toBe('google-123')
        ->and($user->password)->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->onboarded_at)->toBeNull()
        ->and($user->referral_code)->toStartWith('CETAR')
        ->and(auth()->id())->toBe($user->id);
});

test('an existing email account is linked to google and logged in', function () {
    $user = User::factory()->create(['email' => 'lama@gmail.com']);

    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeGoogleUser(id: 'google-777', email: 'lama@gmail.com'));

    $this->get('/auth/google/callback')->assertRedirect(route('user.dashboard'));

    expect($user->refresh()->google_id)->toBe('google-777')
        ->and(auth()->id())->toBe($user->id);
});

test('a returning google user just logs in', function () {
    $user = User::factory()->create(['google_id' => 'google-999']);

    Socialite::shouldReceive('driver->user')
        ->andReturn(fakeGoogleUser(id: 'google-999', email: $user->email));

    $this->get('/auth/google/callback')->assertRedirect(route('user.dashboard'));

    expect(User::count())->toBe(1)
        ->and(auth()->id())->toBe($user->id);
});

test('a referral code stashed before the google redirect is applied on signup', function () {
    $referrer = User::factory()->create();

    Socialite::shouldReceive('driver->user')->andReturn(fakeGoogleUser());

    $this->withSession(['referral_code' => $referrer->referral_code])
        ->get('/auth/google/callback');

    expect(User::where('email', 'orang@gmail.com')->sole()->referred_by)->toBe($referrer->id);
});

test('the google redirect stashes the referral code in the session', function () {
    Socialite::shouldReceive('driver->redirect')
        ->andReturn(redirect('https://accounts.google.com/oauth'));

    $this->get('/auth/google/redirect?ref=CETARABCDE')
        ->assertRedirect('https://accounts.google.com/oauth')
        ->assertSessionHas('referral_code', 'CETARABCDE');
});

test('a failed google handshake bounces back to login with an error', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new RuntimeException('state mismatch'));

    $this->get('/auth/google/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHas('error');

    expect(auth()->check())->toBeFalse();
});

test('form login with a passwordless google account fails cleanly', function () {
    $user = User::factory()->create(['password' => null, 'google_id' => 'google-123']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'tebakan-apapun')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});
