<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prodi;
use Hamcrest\NullDescription;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        // Ambil Role yang sudah dibuat
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleBaak = Role::where('name', 'baak')->first();
        $roleDirektur = Role::where('name', 'direktur')->first();
        $roleWadir1 = Role::where('name', 'wadir1')->first();
        $roleWadir2 = Role::where('name', 'wadir2')->first();
        $roleWadir3 = Role::where('name', 'wadir3')->first();
        $roleKaprodi = Role::where('name', 'kaprodi')->first();
        $roleDosen = Role::where('name', 'dosen')->first();

        $admin = User::firstOrCreate([
            'email' => 'admin@poltek.ac.id'
        ], [
            'name' => 'Super Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleAdmin) $admin->assignRole($roleAdmin);

        $direktur = User::firstOrCreate([
            'email' => 'suci.purwandari@poltek.ac.id'
        ], [
            'name' => 'Ir. Suci Purwandari, MM, Ph.D',
            'username' => 'suci.purwandari',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleDirektur) $direktur->assignRole($roleDirektur);

        $wadir1 = User::firstOrCreate([
            'email' => 'edy.susena@poltek.ac.id'
        ], [
            'name' => 'Edy Susena, M.Kom',
            'username' => 'edy.susena',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir1) $wadir1->assignRole($roleWadir1, $roleDosen);

        $wadir2 = User::firstOrCreate([
            'email' => 'canggih.ajika@poltek.ac.id'
        ], [
            'name' => 'Canggih Ajika P, M.Kom, Ph.D',
            'username' => 'canggih.ajika',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir2) $wadir2->assignRole($roleWadir2, $roleDosen);

        $wadir3 = User::firstOrCreate([
            'email' => 'wachid.yahya@poltek.ac.id'
        ], [
            'name' => 'Wachid Yahya, M.Pd., Ph.D',
            'username' => 'wachid.yahya',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleWadir3) $wadir3->assignRole($roleWadir3, $roleDosen);

        
        $dosenList = [
            ['Dody Mulyanto, MM', 'dody.mulyanto'], //Akuntansi Perpajakan
            ['Agustyarum Pradiska Budi, ME', 'agustyarum.pradiska'], //Bisnis & Management Retail
            ['Markus Utomo Sukendar, S.Sos., M.I.Kom', 'markus.utomo'], //Produksi Media
            ['Wahyu Tri H, S.Pd.,M.M', 'wahyu.tri'], //Perhotelan
            ['Dwi Iskandar, M.Kom', 'dwi.iskandar'], //Teknologi Rekayasa Perangkat Lunak
            ['Sudiro, ST, M.Si', 'sudiro'], //Teknologi Rekayasa Otomotif
            ['apt. Iin Suhesti, M.Farm.', 'iin.suhesti'], //Farmasi
            ['Emma Ismawatie, S.ST., M.Kes', 'emma.ismawatie'], //Teknologi Laboratorium Medis
            ['Frestiany Regina Putri, M.Kom', 'frestiany.regina'], //Manajemen Informasi Kesehatan
        ];

        foreach ($dosenList as $dosenData) {
            $user = User::firstOrCreate(['email' => $dosenData[1] . '@poltekindonusa.ac.id' ], [
                'name' => $dosenData[0], 
                'username' => $dosenData[1], 
                'password' => Hash::make('password'),
                'nidn' => Null, 
            ]);
            if ($roleDosen) $user->assignRole($roleDosen, $roleKaprodi);
        }

        $baak = User::firstOrCreate([
            'email' => 'baak@poltek.ac.id'
        ], [
            'name' => 'Dewi Amelia, M.Kom',
            'username' => 'baak',
            'password' => Hash::make('password'),
            'signature_path' => null,
        ]);
        if ($roleBaak) $baak->assignRole($roleBaak);
    }
}
