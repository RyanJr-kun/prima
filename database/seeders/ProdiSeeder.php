<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataProdi = [
            // --- KAMPUS 1 (TEKNIK, BISNIS, PERHOTELAN) ---
            [
                'code' => 'AP',
                'name' => 'Akuntansi Perpajakan',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 6,
                'primary_campus' => 'kampus_1'
            ],
            [
                'code' => 'BMR',
                'name' => 'Bisnis & Management Retail',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 7,
                'primary_campus' => 'kampus_1'
            ],
            [
                'code' => 'PM',
                'name' => 'Produksi Media',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 8,
                'primary_campus' => 'kampus_1'
            ],
            [
                'code' => 'HT',
                'name' => 'Perhotelan',
                'jenjang' => 'D3',
                'lama_studi' => 6,
                'kaprodi_id' => 9,
                'primary_campus' => 'kampus_1'
            ],
            [
                'code' => 'TRPL',
                'name' => 'Teknologi Rekayasa Perangkat Lunak',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 10,
                'primary_campus' => 'kampus_1'
            ],
            [
                'code' => 'TRO',
                'name' => 'Teknologi Rekayasa Otomotif',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 11,
                'primary_campus' => 'kampus_1'
            ],

            // --- KAMPUS 2 (KESEHATAN) ---
            [
                'code' => 'FM',
                'name' => 'Farmasi',
                'jenjang' => 'D3',
                'lama_studi' => 6,
                'kaprodi_id' => 12,
                'primary_campus' => 'kampus_2' // Sesuai data Lab Farmasi
            ],
            [
                'code' => 'MIK',
                'name' => 'Manajemen Informasi Kesehatan',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 13,
                'primary_campus' => 'kampus_2' // Sesuai data Lab MIK
            ],
            [
                'code' => 'TLM',
                'name' => 'Teknologi Laboratorium Medis',
                'jenjang' => 'D4',
                'lama_studi' => 8,
                'kaprodi_id' => 14,
                'primary_campus' => 'kampus_2' // Sesuai data Lab TLM
            ]
        ];

        foreach ($dataProdi as $prodi) {
            // Gunakan updateOrCreate agar data bisa di-update jika seed dijalankan ulang
            Prodi::updateOrCreate(
                ['code' => $prodi['code']], // Kunci pencarian (Code unik)
                [
                    'name' => $prodi['name'],
                    'jenjang' => $prodi['jenjang'],
                    'lama_studi' => $prodi['lama_studi'],
                    'kaprodi_id' => $prodi['kaprodi_id'],
                    'primary_campus' => $prodi['primary_campus'], // Kolom Baru
                ]
            );
        }
    }
}
