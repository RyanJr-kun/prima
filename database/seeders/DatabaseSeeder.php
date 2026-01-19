<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role (Jabatan)
        // Kita pakai firstOrCreate agar tidak error jika dijalankan berulang
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleDirektur = Role::firstOrCreate(['name' => 'direktur']);
        $roleWadir = Role::firstOrCreate(['name' => 'wadir1']);
        $roleKaprodi = Role::firstOrCreate(['name' => 'kaprodi']);
        $roleDosen = Role::firstOrCreate(['name' => 'dosen']);
        $roleMahasiswa = Role::firstOrCreate(['name' => 'mahasiswa']);

        $prodiTrpl = \App\Models\Prodi::create([
            'code' => 'TRPL',
            'name' => 'Teknologi Rekayasa Perangkat Lunak',
            'jenjang' => 'D4',
            'lama_studi' => '8',
            'kaprodi_id' => null,
        ]);

        // 2. Buat Akun SUPER ADMIN (Anda)
        $admin = User::firstOrCreate([
            'email' => 'admin@poltek.ac.id'
        ], [
            'name' => 'Super Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'), // Password default
            'signature_path' => null,
        ]);
        $admin->assignRole($roleAdmin);

        // 3. Buat Akun PEJABAT (Sesuai Tanda Tangan Excel)

        // Direktur (Dari Excel Sheet 2)
        $direktur = User::firstOrCreate([
            'email' => 'suci@poltek.ac.id'
        ], [
            'name' => 'Ir. Suci Purwandari, MM, Ph.D',
            'username' => 'direktur',
            'password' => Hash::make('password'),
            'nidn' => '001', // Dummy
        ]);
        $direktur->assignRole($roleDirektur);
        $direktur->assignRole($roleDosen); // Direktur juga seorang Dosen

        // Wakil Direktur 1
        $wadir = User::firstOrCreate([
            'email' => 'edy.susena@poltek.ac.id'
        ], [
            'name' => 'Edy Susena, M.Kom',
            'username' => 'wadir1', // Bisa login pakai username ini
            'password' => Hash::make('password'),
            'nidn' => '002',
            'prodi_id' => $prodiTrpl->id,
        ]);
        $wadir->assignRole($roleWadir);
        $wadir->assignRole($roleDosen);

        // Kaprodi
        $kaprodi = User::firstOrCreate([
            'email' => 'dwi.iskandar@poltek.ac.id'
        ], [
            'name' => 'Dwi Iskandar, M.Kom',
            'username' => 'kaprodi',
            'password' => Hash::make('password'),
            'nidn' => '003',
            'prodi_id' => $prodiTrpl->id,
        ]);
        $kaprodi->assignRole($roleKaprodi);
        $kaprodi->assignRole($roleDosen);
        
        $prodiTrpl->update([
        'kaprodi_id' => $kaprodi->id
        ]);

        // 4. Buat Akun DOSEN LAIN (Sesuai Distribusi Matkul)

        $dosenList = [
            ['Agung Wibiyanto, SS., MM', 'agung'],
            ['Dewi Amelia Lestari, S.Kom., MM.Par', 'dewi'],
            ['Frestiany Regina Putri, M.Kom', 'frestiany'],
            ['Wasis Waluyo, S.Kom', 'wasis'],
            ['Muhammad Nurfauzi Sahono, M.Kom', 'fauzi'],
            ['Canggih Ajika P, M.Kom, Ph.D', 'canggih'], // Beliau ada di Sheet 1 sbg Direktur, kita masukkan sbg Dosen senior juga
        ];

        foreach ($dosenList as $dosenData) {
            $user = User::firstOrCreate(['email' => $dosenData[1] . '@poltek.ac.id' ], 
            [
                'name' => $dosenData[0],
                'username' => $dosenData[1],
                'password' => Hash::make('password'),
                'nidn' => rand(10000, 99999), 
            ]);
            $user->assignRole($roleDosen);
        }

        $periode = \App\Models\AcademicPeriod::create([
            'name' => 'Ganjil 2025/2026',
            'is_active' => true,
            'distribution_status' => \App\Enums\DistributionStatus::DRAFT,
        ]);

        $kurikulum = \App\Models\Kurikulum::create([
            'name' => 'Kurikulum 2023',
            'tanggal' => '2023-01-01',
            'semester' => '7',
            'prodi_id' => $prodiTrpl->id,
            'is_active' => true,
            'file_path' => null,
        ]);
    }
}
