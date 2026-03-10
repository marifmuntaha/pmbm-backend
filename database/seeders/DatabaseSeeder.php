<?php

namespace Database\Seeders;

use Database\Seeders\Payment\GatewaySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(GatewaySeeder::class);
        $this->call(YearSeeder::class);
        $this->call(InstitutionSeeder::class);
        $this->call(RuleSeeder::class);
        $this->call(ScheduleSeeder::class);
    }
}
