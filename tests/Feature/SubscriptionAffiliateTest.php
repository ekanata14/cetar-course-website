<?php

use App\Actions\Affiliate\ProcessWithdrawal;
use App\Actions\Affiliate\RequestWithdrawal;
use App\Actions\Subscription\SettlePayment;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\WithdrawalStatus;
use App\Livewire\Admin\Withdrawal\Index as AdminWithdrawals;
use App\Livewire\User\Packages;
use App\Models\AffiliateCommission;
use App\Models\Package;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Withdrawal;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

/**
 * Helper: payment pending untuk user pada plan 30 hari seharga 100.000.
 */
function pendingPayment(?User $user = null): Payment
{
    $user ??= User::factory()->create();
    $plan = PackagePlan::factory()->create(['duration_days' => 30, 'price' => 100000]);

    return Payment::create([
        'user_id' => $user->id,
        'package_plan_id' => $plan->id,
        'amount' => $plan->price,
        'status' => PaymentStatus::Pending,
        'external_id' => 'CETAR-INV-TEST-'.uniqid(),
    ]);
}

// ==========================================
// PROVISIONING
// ==========================================

test('settling a payment provisions a 30 day subscription', function () {
    $payment = pendingPayment();

    app(SettlePayment::class)->execute($payment);

    $subscription = UserSubscription::where('user_id', $payment->user_id)->first();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Settled)
        ->and($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->expires_at->isSameDay(now()->addDays(30)))->toBeTrue();
});

test('settling a second payment extends the running subscription', function () {
    $payment = pendingPayment();
    app(SettlePayment::class)->execute($payment);

    // Pembayaran kedua pada plan yang sama, 10 hari kemudian
    $this->travel(10)->days();
    $second = Payment::create([
        'user_id' => $payment->user_id,
        'package_plan_id' => $payment->package_plan_id,
        'amount' => 100000,
        'status' => PaymentStatus::Pending,
        'external_id' => 'CETAR-INV-TEST-2ND',
    ]);
    app(SettlePayment::class)->execute($second);

    $subscription = UserSubscription::where('user_id', $payment->user_id)->sole();

    // Sisa 20 hari + 30 hari baru = berakhir 50 hari dari sekarang
    expect($subscription->expires_at->isSameDay(now()->addDays(50)))->toBeTrue();
});

test('an expired subscription is reactivated starting from now', function () {
    $payment = pendingPayment();
    $plan = $payment->packagePlan;

    UserSubscription::create([
        'user_id' => $payment->user_id,
        'package_id' => $plan->package_id,
        'status' => SubscriptionStatus::Expired,
        'expires_at' => now()->subMonth(), // Sudah lama kedaluwarsa
    ]);

    app(SettlePayment::class)->execute($payment);

    $subscription = UserSubscription::where('user_id', $payment->user_id)->sole();

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->expires_at->isSameDay(now()->addDays(30)))->toBeTrue();
});

test('settling is idempotent — replayed webhooks do not double provision', function () {
    $payment = pendingPayment();

    app(SettlePayment::class)->execute($payment);
    app(SettlePayment::class)->execute($payment->refresh()); // Replay

    expect(UserSubscription::where('user_id', $payment->user_id)->count())->toBe(1)
        ->and(
            UserSubscription::where('user_id', $payment->user_id)->sole()
                ->expires_at->isSameDay(now()->addDays(30))
        )->toBeTrue();
});

// ==========================================
// KOMISI AFILIASI
// ==========================================

test('a settled payment credits commission to the referrer wallet', function () {
    $referrer = User::factory()->create(['wallet_balance' => 0]);
    $buyer = User::factory()->create(['referred_by' => $referrer->id]);

    $payment = pendingPayment($buyer);
    app(SettlePayment::class)->execute($payment);

    $commission = AffiliateCommission::sole();

    // 10% dari 100.000 = 10.000
    expect((float) $commission->amount)->toBe(10000.0)
        ->and($commission->referrer_id)->toBe($referrer->id)
        ->and($commission->referred_id)->toBe($buyer->id)
        ->and((float) $referrer->refresh()->wallet_balance)->toBe(10000.0);
});

test('no commission is created when the buyer has no referrer', function () {
    $payment = pendingPayment();
    app(SettlePayment::class)->execute($payment);

    expect(AffiliateCommission::count())->toBe(0);
});

test('commission is idempotent per payment', function () {
    $referrer = User::factory()->create(['wallet_balance' => 0]);
    $buyer = User::factory()->create(['referred_by' => $referrer->id]);
    $payment = pendingPayment($buyer);

    app(SettlePayment::class)->execute($payment);
    // Panggil distributor langsung (simulasi replay yang lolos guard status)
    app(\App\Actions\Affiliate\DistributeCommission::class)->execute($payment->refresh());

    expect(AffiliateCommission::count())->toBe(1)
        ->and((float) $referrer->refresh()->wallet_balance)->toBe(10000.0);
});

// ==========================================
// PENARIKAN SALDO
// ==========================================

test('requesting a withdrawal holds the funds immediately', function () {
    $user = User::factory()->create(['wallet_balance' => 100000]);

    $withdrawal = app(RequestWithdrawal::class)->execute($user, 60000, [
        'bank_name' => 'BCA', 'account_number' => '123', 'account_name' => 'Budi',
    ]);

    expect($withdrawal->status)->toBe(WithdrawalStatus::Pending)
        ->and((float) $user->refresh()->wallet_balance)->toBe(40000.0);
});

test('withdrawals below the minimum or above the balance are rejected', function () {
    $user = User::factory()->create(['wallet_balance' => 100000]);
    $bank = ['bank_name' => 'BCA', 'account_number' => '123', 'account_name' => 'Budi'];

    expect(fn () => app(RequestWithdrawal::class)->execute($user, 10000, $bank))
        ->toThrow(ValidationException::class); // Di bawah minimum 50.000

    expect(fn () => app(RequestWithdrawal::class)->execute($user, 150000, $bank))
        ->toThrow(ValidationException::class); // Melebihi saldo

    expect((float) $user->refresh()->wallet_balance)->toBe(100000.0); // Saldo utuh
});

test('approving a withdrawal marks it success without refunding', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $user = User::factory()->create(['wallet_balance' => 100000]);
    $withdrawal = app(RequestWithdrawal::class)->execute($user, 60000, [
        'bank_name' => 'BCA', 'account_number' => '123', 'account_name' => 'Budi',
    ]);

    app(ProcessWithdrawal::class)->execute($withdrawal, $admin, approve: true);

    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Success)
        ->and($withdrawal->processed_by)->toBe($admin->id)
        ->and((float) $user->refresh()->wallet_balance)->toBe(40000.0);
});

test('rejecting a withdrawal refunds the held funds', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $user = User::factory()->create(['wallet_balance' => 100000]);
    $withdrawal = app(RequestWithdrawal::class)->execute($user, 60000, [
        'bank_name' => 'BCA', 'account_number' => '123', 'account_name' => 'Budi',
    ]);

    app(ProcessWithdrawal::class)->execute($withdrawal, $admin, approve: false);

    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Rejected)
        ->and((float) $user->refresh()->wallet_balance)->toBe(100000.0);
});

// ==========================================
// HALAMAN LIVEWIRE
// ==========================================

test('the package catalog renders and checkout creates a pending invoice', function () {
    $user = User::factory()->create();
    $package = Package::factory()->create();
    $plan = PackagePlan::factory()->for($package)->create(['price' => 99000]);

    $this->actingAs($user)->get(route('user.packages'))->assertOk();

    Livewire::actingAs($user)
        ->test(\App\Livewire\User\Checkout::class, ['plan' => $plan])
        ->call('pay');

    $payment = Payment::sole();

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->external_id)->toStartWith('CETAR-INV-')
        ->and((float) $payment->amount)->toBe(99000.0);

    // Bayar kedua kali memakai invoice pending yang sama
    Livewire::actingAs($user)->test(\App\Livewire\User\Checkout::class, ['plan' => $plan])->call('pay');
    expect(Payment::count())->toBe(1);
});

test('the affiliate page renders with the referral link', function () {
    $user = User::factory()->create(['referral_code' => 'CETARLINK1']);

    $this->actingAs($user)
        ->get(route('user.affiliate'))
        ->assertOk()
        ->assertSee('CETARLINK1');
});

test('a user can request a withdrawal from the affiliate page', function () {
    $user = User::factory()->create(['wallet_balance' => 200000]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\User\Affiliate::class)
        ->call('openWithdrawForm')
        ->set('amount', '75000')
        ->set('bankName', 'BCA')
        ->set('accountNumber', '1234567890')
        ->set('accountName', 'Budi Santoso')
        ->call('requestWithdrawal')
        ->assertHasNoErrors();

    expect(Withdrawal::count())->toBe(1)
        ->and((float) $user->refresh()->wallet_balance)->toBe(125000.0);
});

test('admin can approve a withdrawal from the queue page', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $user = User::factory()->create(['wallet_balance' => 100000]);
    $withdrawal = app(RequestWithdrawal::class)->execute($user, 50000, [
        'bank_name' => 'BCA', 'account_number' => '123', 'account_name' => 'Budi',
    ]);

    $this->actingAs($admin)->get(route('admin.withdrawals'))->assertOk();

    Livewire::actingAs($admin)
        ->test(AdminWithdrawals::class)
        ->call('approve', $withdrawal->id);

    expect($withdrawal->refresh()->status)->toBe(WithdrawalStatus::Success);
});
