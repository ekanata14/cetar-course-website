<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Translatable\HasTranslations;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasTranslations, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'referral_code',
        'referred_by',
        'wallet_balance',
        'profile_photo',
        'timezone',
        'locale',
        'preferences',
        'google_id',
        'onboarded_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'onboarded_at' => 'datetime',
        'password' => 'hashed',
        'wallet_balance' => 'decimal:2',
        'preferences' => 'array',
        'timezone' => 'string',
        'locale' => 'string',
    ];

    // ==========================================
    // RELATIONS
    // ==========================================

    /** Langganan paket milik user */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(UserQuizAttempt::class);
    }

    /** Kemajuan belajar user pada item roadmap */
    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    /** Komisi yang diterima user ini sebagai referrer */
    public function referralCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'referrer_id');
    }

    /** User yang mengundang user ini (dari ?ref= saat registrasi) */
    public function referrer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    // ==========================================
    // HELPERS (query sederhana, bukan business logic)
    // ==========================================

    /** Cek apakah user memegang langganan aktif untuk sebuah paket (gate akses konten) */
    public function hasActiveSubscription(Package|int $package): bool
    {
        $packageId = $package instanceof Package ? $package->id : $package;

        return $this->subscriptions()
            ->where('package_id', $packageId)
            ->active()
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /** User baru diarahkan ke onboarding sampai menyelesaikan/melewatinya */
    public function hasOnboarded(): bool
    {
        return $this->onboarded_at !== null;
    }

    /**
     * Helper untuk mengambil inisial nama (Untuk profile_photo).
     * Contoh: "Budi Santoso" -> "BS", "Admin" -> "AD"
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);

        // Jika nama terdiri dari 2 kata atau lebih (Contoh: Budi Santoso)
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr(end($words), 0, 1));
        }

        // Jika hanya 1 kata (Contoh: Admin), ambil 2 huruf pertama
        return strtoupper(substr($this->name, 0, 2));
    }
}
