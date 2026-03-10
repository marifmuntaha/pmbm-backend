<?php

namespace Database\Seeders\Payment;
use App\Models\Payment\Gateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Gateway::updateOrCreate(
            ['provider' => 'midtrans'],
            [
                'is_active' => true,
                'mode' => 1,
                'server_key' => 'Mid-server-xxxxxxxxxx',
                'client_key' => 'Mid-client-xxxxxxxxxx',
            ]
        );
    }
}
