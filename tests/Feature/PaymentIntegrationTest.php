<?php

use App\Actions\Payment\CreateDokuPayment;
use App\Enums\PaymentStatus;
use App\Livewire\User\Checkout;
use App\Mail\InvoiceCreated;
use App\Mail\PaymentReceipt;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Payment\DokuClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    // Kredensial sandbox palsu untuk test
    config()->set('services.doku', [
        'client_id' => 'TEST-CLIENT-ID',
        'secret_key' => 'test-secret-key',
        'base_url' => 'https://api-sandbox.doku.com',
    ]);
});

/**
 * Helper: payment pending milik user.
 */
function dokuPendingPayment(): Payment
{
    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 100000, 'duration_days' => 30]);

    return Payment::create([
        'user_id' => $user->id,
        'package_plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => PaymentStatus::Pending,
        'external_id' => 'CETAR-INV-DOKU-TEST',
    ]);
}

/**
 * Helper: kirim webhook DOKU dengan signature valid (dihitung dengan skema yang sama).
 */
function postDokuWebhook(array $payload, array $headerOverrides = [])
{
    $body = json_encode($payload);
    $requestId = 'webhook-req-1';
    $timestamp = '2026-07-04T12:00:00Z';

    $signature = app(DokuClient::class)->signature($requestId, $timestamp, '/webhooks/doku', $body);

    return test()->call('POST', '/webhooks/doku', [], [], [], array_merge([
        'CONTENT_TYPE' => 'application/json',
        'HTTP_CLIENT-ID' => 'TEST-CLIENT-ID',
        'HTTP_REQUEST-ID' => $requestId,
        'HTTP_REQUEST-TIMESTAMP' => $timestamp,
        'HTTP_SIGNATURE' => $signature,
    ], $headerOverrides), $body);
}

// ==========================================
// DOKU CLIENT
// ==========================================

test('the doku client requests a checkout session with signed headers', function () {
    Http::fake([
        'api-sandbox.doku.com/checkout/v1/payment' => Http::response([
            'response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/link/ABC123']],
        ]),
    ]);

    $payment = dokuPendingPayment();
    $result = app(CreateDokuPayment::class)->execute($payment);

    expect($result->payment_url)->toBe('https://sandbox.doku.com/checkout/link/ABC123');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Client-Id', 'TEST-CLIENT-ID')
            && $request->hasHeader('Request-Id')
            && $request->hasHeader('Request-Timestamp')
            && str_starts_with($request->header('Signature')[0], 'HMACSHA256=')
            && $request['order']['invoice_number'] === 'CETAR-INV-DOKU-TEST'
            && $request['order']['amount'] === 100000
            // URL kembali setelah user menyelesaikan pembayaran di halaman DOKU
            && $request['order']['callback_url'] === route('user.packages', ['status' => 'payment-return']);
    });
});

test('checkout survives a doku api failure with an error toast instead of crashing', function () {
    Mail::fake();
    Http::fake(['api-sandbox.doku.com/*' => Http::response(['error' => 'internal'], 500)]);

    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    Livewire::actingAs($user)
        ->test(Checkout::class, ['plan' => $plan])
        ->call('pay')
        ->assertOk()
        ->assertNoRedirect();

    // Invoice pending tetap tercatat agar bisa dicoba ulang; tidak ada URL bayar
    expect(Payment::sole()->payment_url)->toBeNull();
});

test('checkout in production without doku credentials surfaces an error, not a silent success', function () {
    Mail::fake();
    config()->set('services.doku.client_id', null);
    config()->set('services.doku.secret_key', null);
    app()['env'] = 'production';

    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    Livewire::actingAs($user)
        ->test(Checkout::class, ['plan' => $plan])
        ->call('pay')
        ->assertOk()
        ->assertNoRedirect();

    app()['env'] = 'testing';
});

test('checkout degrades gracefully when doku credentials are absent', function () {
    config()->set('services.doku.client_id', null);
    config()->set('services.doku.secret_key', null);
    Http::fake(); // Tidak boleh ada HTTP call sama sekali

    $payment = dokuPendingPayment();
    $result = app(CreateDokuPayment::class)->execute($payment);

    expect($result->payment_url)->toBeNull();
    Http::assertNothingSent();
});

// ==========================================
// WEBHOOK
// ==========================================

test('a valid SUCCESS webhook settles the payment and provisions access', function () {
    Mail::fake();
    $payment = dokuPendingPayment();

    postDokuWebhook([
        'order' => ['invoice_number' => 'CETAR-INV-DOKU-TEST', 'amount' => 100000],
        'transaction' => ['status' => 'SUCCESS'],
    ])->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Settled)
        ->and(UserSubscription::where('user_id', $payment->user_id)->count())->toBe(1);

    // Kwitansi dikirim ke pembeli
    Mail::assertQueued(PaymentReceipt::class, fn ($mail) => $mail->hasTo($payment->user->email));
});

test('a webhook with an invalid signature is rejected and changes nothing', function () {
    $payment = dokuPendingPayment();

    postDokuWebhook(
        [
            'order' => ['invoice_number' => 'CETAR-INV-DOKU-TEST'],
            'transaction' => ['status' => 'SUCCESS'],
        ],
        ['HTTP_SIGNATURE' => 'HMACSHA256=tandatanganpalsu'],
    )->assertUnauthorized();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending);
});

test('a FAILED webhook marks the payment failed without provisioning', function () {
    $payment = dokuPendingPayment();

    postDokuWebhook([
        'order' => ['invoice_number' => 'CETAR-INV-DOKU-TEST'],
        'transaction' => ['status' => 'FAILED'],
    ])->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Failed)
        ->and(UserSubscription::count())->toBe(0);
});

test('a FAILED replay never downgrades an already settled payment', function () {
    Mail::fake();
    $payment = dokuPendingPayment();

    postDokuWebhook([
        'order' => ['invoice_number' => 'CETAR-INV-DOKU-TEST'],
        'transaction' => ['status' => 'SUCCESS'],
    ])->assertOk();

    postDokuWebhook([
        'order' => ['invoice_number' => 'CETAR-INV-DOKU-TEST'],
        'transaction' => ['status' => 'FAILED'],
    ])->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Settled);
});

test('a webhook for an unknown invoice returns 404', function () {
    postDokuWebhook([
        'order' => ['invoice_number' => 'INVOICE-TIDAK-ADA'],
        'transaction' => ['status' => 'SUCCESS'],
    ])->assertNotFound();
});

// ==========================================
// EMAIL + ALUR CHECKOUT
// ==========================================

test('checkout queues the invoice email once and redirects to the payment url', function () {
    Mail::fake();
    Http::fake([
        'api-sandbox.doku.com/*' => Http::response([
            'response' => ['payment' => ['url' => 'https://sandbox.doku.com/checkout/link/XYZ']],
        ]),
    ]);

    $user = User::factory()->create();
    $plan = PackagePlan::factory()->create(['price' => 99000]);

    Livewire::actingAs($user)
        ->test(Checkout::class, ['plan' => $plan])
        ->call('pay')
        ->assertRedirect('https://sandbox.doku.com/checkout/link/XYZ');

    $payment = Payment::sole();
    expect($payment->payment_url)->toBe('https://sandbox.doku.com/checkout/link/XYZ');

    // Email invoice hanya sekali — bayar kedua kali memakai invoice yang sama
    Livewire::actingAs($user)->test(Checkout::class, ['plan' => $plan])->call('pay');

    Mail::assertQueuedCount(1);
    Mail::assertQueued(InvoiceCreated::class, fn ($mail) => $mail->hasTo($user->email));
});

test('invoice and receipt emails render with brand content', function () {
    $payment = dokuPendingPayment();
    $payment->update(['payment_url' => 'https://sandbox.doku.com/pay/1']);

    $invoiceHtml = (new InvoiceCreated($payment))->render();
    expect($invoiceHtml)
        ->toContain('CETAR-INV-DOKU-TEST')
        ->toContain('Bayar Sekarang')
        ->toContain('100.000');

    $subscription = UserSubscription::create([
        'user_id' => $payment->user_id,
        'package_id' => $payment->packagePlan->package_id,
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]);

    $receiptHtml = (new PaymentReceipt($payment, $subscription))->render();
    expect($receiptHtml)
        ->toContain('Pembayaran Diterima')
        ->toContain($subscription->expires_at->translatedFormat('d M Y'));
});
