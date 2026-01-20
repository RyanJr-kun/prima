<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prodi::firstOrCreate([
            'code' => 'AP',
            'name' => 'Akuntansi Perpajakan',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '6',
        ]);
        Prodi::firstOrCreate([
            'code' => 'BMR',
            'name' => 'Bisnis & Management Retail',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '7',
        ]);
        Prodi::firstOrCreate([
            'code' => 'PM',
            'name' => 'Produksi Media',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '8',
        ]);
        Prodi::firstOrCreate([
            'code' => 'HT',
            'name' => 'Perhotelan',
            'jenjang' => 'D3',
            'lama_studi' => '6',
            'kaprodi_id' => '9',
        ]);
        Prodi::firstOrCreate([
            'code' => 'TRPL',
            'name' => 'Teknologi Rekayasa Perangkat Lunak',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '10',
        ]);
        Prodi::firstOrCreate([
            'code' => 'TRO',
            'name' => 'Teknologi Rekayasa Otomotif',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '11',
        ]);
        Prodi::firstOrCreate([
            'code' => 'FM',
            'name' => 'Farmasi',
            'jenjang' => 'D3',
            'lama_studi' => '6',
            'kaprodi_id' => '12',
        ]);
        Prodi::firstOrCreate([
            'code' => 'MIK',
            'name' => 'Manajemen Informasi Kesehatan',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '13',
        ]);
        Prodi::firstOrCreate([
            'code' => 'TLM',
            'name' => 'Teknologi Laboratorium Medis',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => '14',
        ]);

    }
}
