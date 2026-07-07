<?php

use App\Livewire\Admin\Package\Index;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'super_admin']);
});

test('packages page renders for super admin', function () {
    $this->actingAs($this->admin)
        ->get('/admin/packages')
        ->assertOk();
});

test('super admin can create a package with plans', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openCreate')
        ->set('name', 'Pejuang PPPK 2027')
        ->set('description', 'Paket lengkap PPPK.')
        ->set('plans', [
            ['id' => null, 'name' => '1 Bulan', 'duration_days' => 30, 'price' => 99000],
            ['id' => null, 'name' => '1 Tahun', 'duration_days' => 365, 'price' => 499000],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $package = Package::where('name', 'Pejuang PPPK 2027')->first();

    expect($package)->not->toBeNull()
        ->and($package->slug)->toBe('pejuang-pppk-2027')
        ->and($package->plans)->toHaveCount(2);
});

test('updating a package syncs its plans', function () {
    $package = Package::factory()->create(['name' => 'Paket Lama']);
    $keep = PackagePlan::factory()->for($package)->create(['name' => '1 Bulan']);
    $remove = PackagePlan::factory()->for($package)->create(['name' => '3 Bulan']);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openEdit', $package->id)
        ->set('name', 'Paket Baru')
        ->set('plans', [
            // Plan lama dipertahankan (dengan id), plan '3 Bulan' dibuang, plan baru ditambah
            ['id' => $keep->id, 'name' => '1 Bulan Promo', 'duration_days' => 30, 'price' => 79000],
            ['id' => null, 'name' => '6 Bulan', 'duration_days' => 180, 'price' => 299000],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $package->refresh();

    expect($package->name)->toBe('Paket Baru')
        ->and($package->plans)->toHaveCount(2)
        ->and($package->plans()->pluck('name')->all())->toContain('1 Bulan Promo', '6 Bulan')
        ->and(PackagePlan::find($remove->id))->toBeNull();
});

test('super admin can toggle and delete a package', function () {
    $package = Package::factory()->create(['is_active' => true]);

    $component = Livewire::actingAs($this->admin)->test(Index::class);

    $component->call('toggleActive', $package->id);
    expect($package->refresh()->is_active)->toBeFalse();

    $component->call('delete', $package->id);
    expect(Package::find($package->id))->toBeNull();
});

test('a package form requires at least one plan', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openCreate')
        ->set('name', 'Paket Tanpa Plan')
        ->set('plans', [])
        ->call('save')
        ->assertHasErrors(['plans']);
});

test('regular users cannot open the packages page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/packages')
        ->assertRedirect(route('user.dashboard', absolute: false));
});
