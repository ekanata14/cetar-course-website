<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'package_plan_id', 'amount', 'status', 'external_id', 'payment_url'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packagePlan(): BelongsTo
    {
        return $this->belongsTo(PackagePlan::class);
    }

    /** Komisi afiliasi yang dihasilkan pembayaran ini (jika pembeli punya referrer) */
    public function affiliateCommission(): HasOne
    {
        return $this->hasOne(AffiliateCommission::class);
    }
}
