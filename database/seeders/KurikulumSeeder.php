<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KurikulumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = Prodi::all();

        foreach ($prodis as $prodi) {
            Kurikulum::firstOrCreate([
                'prodi_id' => $prodi->id,
                'name' => $prodi->jenjang . ' ' . $prodi->name,
            ], [
                'tanggal' => '2025-01-01',
                'is_active' => true,
            ]);
        }
    }
}
