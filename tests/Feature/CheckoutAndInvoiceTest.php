<?php

use App\Enums\PaymentStatus;
use App\Livewire\User\Checkout;
use App\Livewire\User\Transactions;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.doku', [
        'client_id' => 'TEST-CLIENT-ID',
        'secret_key' => 'test-secret-key',
        'base_url' => 'https://api-sandbox.doku.com',
    ]);
});

/**
 * Helper: payment milik user dengan status tertentu.
 */
function paymentFor(User $user, string $status = 'pending', array $extra = []): Payment
{
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    return Payment::create(array_merge([
        'user_id' => $user->id,
        'package_plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => $status,
        'external_id' => 'CETAR-INV-'.strtoupper(uniqid()),
    ], $extra));
}

// ==========================================
// HALAMAN CHECKOUT
// ==========================================

test('the checkout page renders the order summary for an active plan', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create(['name' => 'Pejuang CPNS 2026']);
    $plan = PackagePlan::factory()->for($package)->create(['name' => '1 Bulan', 'duration_days' => 30, 'price' => 99000]);

    $this->actingAs($user)
        ->get(route('user.checkout', $plan))
        ->assertOk()
        ->assertSee('Pejuang CPNS 2026')
        ->assertSee('1 Bulan')
        ->assertSee('Rp99.000')
        ->assertSee($user->email);
});

test('checkout for a plan of an inactive package returns 404', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create(['is_active' => false]);
    $plan = PackagePlan::factory()->for($package)->create();

    $this->actingAs($user)
        ->get(route('user.checkout', $plan))
        ->assertNotFound();
});

test('the checkout page warns about an existing pending invoice for the same plan', function () {
    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    $pending = Payment::create([
        'user_id' => $user->id,
        'package_plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => PaymentStatus::Pending,
        'external_id' => 'CETAR-INV-PENDINGWARN',
    ]);

    $this->actingAs($user)
        ->get(route('user.checkout', $plan))
        ->assertOk()
        ->assertSee('CETAR-INV-PENDINGWARN');
});

test('paying from the checkout page redirects to the doku payment url', function () {
    Mail::fake();
    Http::fake([
        'api-sandbox.doku.com/*' => Http::response([
            'response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/link/CHK1']],
        ]),
    ]);

    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    Livewire::actingAs($user)
        ->test(Checkout::class, ['plan' => $plan])
        ->call('pay')
        ->assertRedirect('https://sandbox.doku.com/checkout/link/CHK1');
});

// ==========================================
// INVOICE PDF
// ==========================================

test('the owner can download the invoice as a pdf', function () {
    $user = User::factory()->create();
    $payment = paymentFor($user, 'settled');

    $response = $this->actingAs($user)->get(route('user.invoice.download', $payment));

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->headers->get('content-disposition'))
        ->toContain('invoice-'.$payment->external_id.'.pdf');
});

test('another user cannot download someone else\'s invoice', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $payment = paymentFor($owner);

    $this->actingAs($intruder)
        ->get(route('user.invoice.download', $payment))
        ->assertForbidden();
});

// ==========================================
// RIWAYAT TRANSAKSI
// ==========================================

test('the transactions page lists only the user\'s own payments', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = paymentFor($user, 'settled');
    $notMine = paymentFor($other, 'settled');

    $this->actingAs($user)
        ->get(route('user.transactions'))
        ->assertOk()
        ->assertSee($mine->external_id)
        ->assertDontSee($notMine->external_id);
});

test('the status filter narrows the transaction list', function () {
    $user = User::factory()->create();
    $settled = paymentFor($user, 'settled');
    $pending = paymentFor($user, 'pending');

    Livewire::actingAs($user)
        ->test(Transactions::class)
        ->set('filter', 'settled')
        ->assertSee($settled->external_id)
        ->assertDontSee($pending->external_id);
});

test('a pending payment with a url shows a pay action', function () {
    $user = User::factory()->create();
    paymentFor($user, 'pending', ['payment_url' => 'https://sandbox.doku.com/pay/999']);

    $this->actingAs($user)
        ->get(route('user.transactions'))
        ->assertOk()
        ->assertSee('https://sandbox.doku.com/pay/999');
});

test('the transactions page paginates at ten per page', function () {
    $user = User::factory()->create();

    $payments = collect(range(1, 12))->map(function ($i) use ($user) {
        $payment = paymentFor($user, 'settled');
        $payment->forceFill(['created_at' => now()->subDays($i)])->save();

        return $payment;
    });

    $newest = $payments->first(); // subDays(1) -> halaman 1
    $oldest = $payments->last();  // subDays(12) -> halaman 2

    Livewire::actingAs($user)
        ->test(Transactions::class)
        ->assertSee($newest->external_id)
        ->assertDontSee($oldest->external_id)
        ->call('gotoPage', 2)
        ->assertSee($oldest->external_id)
        ->assertDontSee($newest->external_id);
});
