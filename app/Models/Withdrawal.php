<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'bank_details', 'status', 'processed_by'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bank_details' => 'array',
            'status' => WithdrawalStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Admin yang memproses (menyetujui/menolak) penarikan */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
