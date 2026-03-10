<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = collect([
            [
                'name' => 'Raudhatul Atfal Darul Hikmah',
                'surname' => 'RA Darul Hikmah',
                'tagline' => 'Cerdas Ceria Berakhlakhul Karimah',
                'npsn' => '123456',
                'nsm' => '1234567890',
                'address' => 'Jl. Raya Jepara - Bugel KM 07 Ds. Menganti Kec. Kedung, Kab. Jepara - Jawa Tengah',
                'phone' => '082229366506',
                'email' => 'ra@darul-hikmah.sch.id',
                'website' => 'https://ra.darul-hikmah.sch.id',
                'head' => 'H. Siti Mualifah, S,Pd',
                'logo' => 'https://images.unsplash.com/photo-1576495199011-eb54e353e20a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
            ],
            [
                'name' => 'Madrasah Ibtidaiyah PTQ Darul Hikmah',
                'surname' => 'MI PTQ Darul Hikmah',
                'tagline' => '100 Siswa 100 Juara',
                'npsn' => '123456',
                'nsm' => '1234567890',
                'address' => 'Jl. Raya Jepara - Bugel KM 07 Ds. Menganti Kec. Kedung, Kab. Jepara - Jawa Tengah',
                'phone' => '082229366506',
                'email' => 'mi@darul-hikmah.sch.id',
                'website' => 'https://mi.darul-hikmah.sch.id',
                'head' => 'Aswad Addu Ali Humad, S,Pd',
                'logo' => 'https://images.unsplash.com/photo-1576495199011-eb54e353e20a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
            ],
            [
                'name' => 'Madrasah Tsanawiyah Darul Hikmah',
                'surname' => 'MTs Darul Hikmah',
                'tagline' => 'Yang penting happy',
                'npsn' => '123456',
                'nsm' => '1234567890',
                'address' => 'Jl. Raya Jepara - Bugel KM 07 Ds. Menganti Kec. Kedung, Kab. Jepara - Jawa Tengah',
                'phone' => '082229366506',
                'email' => 'ra@darul-hikmah.sch.id',
                'website' => 'https://mts.darul-hikmah.sch.id',
                'head' => 'Sholihin, S.Ag.',
                'logo' => 'https://images.unsplash.com/photo-1576495199011-eb54e353e20a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
            ],
            [
                'name' => 'Madrasah Aliyah Darul Hikmah',
                'surname' => 'MA Darul Hikmah',
                'tagline' => 'Great in Character & Knowledge',
                'npsn' => '123456',
                'nsm' => '1234567890',
                'address' => 'Jl. Raya Jepara - Bugel KM 07 Ds. Menganti Kec. Kedung, Kab. Jepara - Jawa Tengah',
                'phone' => '082229366506',
                'email' => 'ma@darul-hikmah.sch.id',
                'website' => 'https://ma.darul-hikmah.sch.id',
                'head' => 'Faiz Noor, S,Pd',
                'logo' => 'https://images.unsplash.com/photo-1576495199011-eb54e353e20a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
            ],
        ]);

        $institutions->map(function ($institution) {
            DB::table('institutions')->updateOrInsert(
                ['name' => $institution['name'], 'surname' => $institution['surname']],
                $institution
            );
        });
    }
}
