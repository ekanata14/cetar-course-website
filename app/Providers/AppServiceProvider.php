<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Super Admin melewati semua pemeriksaan Policy/Gate (lihat system_architecture.md)
        \Illuminate\Support\Facades\Gate::before(
            fn ($user) => $user->isAdmin() ? true : null,
        );

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );

        // Morph map untuk semua relasi polimorfik: `roadmap_items.contentable` dan notifiable pada notifications.
        // enforceMorphMap memastikan tidak ada FQCN yang bocor ke kolom *_type di database.
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'quiz' => \App\Models\Quiz::class,
            'content' => \App\Models\Content::class,
        ]);
    }
}
