<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = collect([
            ['name' => '2025/2026', 'active' => false,  'createdBy' => 1, 'updatedBy' => 1],
            ['name' => '2026/2027', 'active' => true,  'createdBy' => 1, 'updatedBy' => 1],
            ['name' => '2027/2028', 'active' => false,  'createdBy' => 1, 'updatedBy' => 1],
        ]);

        $years->map(function ($year) {
            DB::table('master_years')->updateOrInsert(
                ['name' => $year['name']],
                $year
            );
        });
    }
}
