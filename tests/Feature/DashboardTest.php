<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/user/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit their dashboard', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/user/dashboard')->assertOk();
});

test('regular users are redirected away from the admin dashboard', function () {
    $this->actingAs(User::factory()->create());

    // RoleMiddleware mengarahkan user ke dashboard sesuai role-nya, bukan 403
    $this->get('/admin/dashboard')->assertRedirect(route('user.dashboard', absolute: false));
});

test('super admins can visit the admin dashboard', function () {
    $this->actingAs(User::factory()->create(['role' => 'super_admin']));

    $this->get('/admin/dashboard')->assertOk();
});
