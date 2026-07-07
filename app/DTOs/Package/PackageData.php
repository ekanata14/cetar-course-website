<?php

namespace App\DTOs\Package;

class PackageData
{
    /**
     * @param  array<int, array{id?: int|null, name: string, duration_days: int, price: string|float|int}>  $plans
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $isActive,
        public array $plans = [], // Tier harga bersarang, dikelola bersama paket
    ) {}
}
