<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General Rules
        \App\Models\Master\Rule::updateOrCreate([
            'content' => 'Calon siswa harus mengisi formulir pendaftaran dengan data yang benar dan valid.',
        ]);
        \App\Models\Master\Rule::updateOrCreate([
            'content' => 'Membayar biaya pendaftaran sesuai dengan ketentuan yang berlaku.',
        ]);
        \App\Models\Master\Rule::updateOrCreate([
            'content' => 'Melampirkan dokumen persyaratan seperti Akta Kelahiran, Kartu Keluarga, dan KTP Orang Tua.',
        ]);
        \App\Models\Master\Rule::updateOrCreate([
            'content' => 'Mengikuti tes seleksi masuk yang diselenggarakan oleh panitia PPDB.',
        ]);

        // Specific Rules for Institutions
        $institutions = \App\Models\Institution::all();
        foreach ($institutions as $inst) {
             if (str_contains(strtolower($inst->name), 'aliyah') || str_contains(strtolower($inst->surname), 'ma')) {
                \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Memiliki ijazah MTs/SMP atau sederajat.',
                ]);
                \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Bersedia tinggal di asrama (bagi santri boarding).',
                ]);
            } elseif (str_contains(strtolower($inst->name), 'tsanawiyah') || str_contains(strtolower($inst->surname), 'mts')) {
                \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Memiliki ijazah MI/SD atau sederajat.',
                ]);
                 \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Sudah lancar membaca Al-Qur\'an.',
                ]);
            } elseif (str_contains(strtolower($inst->name), 'ibtidaiyah') || str_contains(strtolower($inst->surname), 'mi')) {
                 \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Berusia minimal 6 tahun pada bulan Juli tahun berjalan.',
                ]);
            }  elseif (str_contains(strtolower($inst->name), 'atfal') || str_contains(strtolower($inst->surname), 'ra')) {
                 \App\Models\Master\Rule::updateOrCreate([
                    'institutionId' => $inst->id,
                    'content' => 'Berusia minimal 4 tahun untuk kelompok A dan 5 tahun untuk kelompok B.',
                ]);
            }
        }
    }
}
