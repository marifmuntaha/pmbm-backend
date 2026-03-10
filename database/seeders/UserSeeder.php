<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
            ['email' => 'marifmuntaha@gmail.com'],
            [
                'name' => 'Muhammad Arif Muntaha',
                'phone' => '6282229366506',
                'phone_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Crypt::encryptString('password'),
                'role' => 1,
                'remember_token' => \Illuminate\Support\Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
