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
        $prodis = [
            ['AP', 'Akuntansi Perpajakan', 'D4', '8', '6'], 
            ['BMR', 'Bisnis & Management Retail', 'D4', '8', '7'], 
            ['PM', 'Produksi Media', 'D4', '8', '8'], 
            ['HT', 'Perhotelan', 'D3', '6', '9'], 
            ['TRPL', 'Teknologi Rekayasa Perangkat Lunak', 'D4', '8', '10'], 
            ['TRO', 'Teknologi Rekayasa Otomotif', 'D4', '8', '11'], 
            ['FM', 'Farmasi', 'D3', '6', '12'], 
            ['MIK', 'Manajemen Informasi Kesehatan', 'D4', '8', '13'],
            ['TLM', 'Teknologi Laboratorium Medis', 'D4', '8', '14']
            ]; 
        
            foreach ($prodis as $prodi) {
            Prodi::firstOrCreate([
                'code' => $prodi[0],
                'name' => $prodi[1],
                'jenjang' => $prodi[2],
                'lama_studi' => $prodi[3], 
                'kaprodi_id' => $prodi[4],
            ]);
        }
    }
}
