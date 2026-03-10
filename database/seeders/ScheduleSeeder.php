<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = collect([
            ["id" => 1, "institutionId" => 1, "event" => "Pembukaan Perndaftaran Gel. I RA Darul Hikmah Menganti", "date" => Carbon::now(), "time" => "-", "place" => "Online", "createdBy" => 1, "updatedBy" => 1],
            ["id" => 2, "institutionId" => 2, "event" => "Pembukaan Perndaftaran Gel. I MI Darul Hikmah Menganti", "date" => Carbon::now(), "time" => "-", "place" => "Online", "createdBy" => 1, "updatedBy" => 1],
            ["id" => 3, "institutionId" => 3, "event" => "Pembukaan Perndaftaran Gel. I MTs Darul Hikmah Menganti", "date" => Carbon::now(), "time" => "-", "place" => "Online", "createdBy" => 1, "updatedBy" => 1],
            ["id" => 4, "institutionId" => 4, "event" => "Pembukaan Perndaftaran Gel. I MA Darul Hikmah Menganti", "date" => Carbon::now(), "time" => "-", "place" => "Online", "createdBy" => 1, "updatedBy" => 1],
        ]);

        $schedules->map(function ($schedule) {
            DB::table('schedules')->updateOrInsert(
                ['id' => $schedule['id']],
                $schedule
            );
        });
    }
}
