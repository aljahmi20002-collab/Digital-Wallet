<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\FeatureManager;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        app(FeatureManager::class)->sync();
    }
}
