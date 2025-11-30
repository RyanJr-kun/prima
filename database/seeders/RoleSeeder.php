<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar Role Project Anda
        $roles = [
            'BAAK',      // Admin Akademik
            'Mahasiswa',
            'Dosen',
            'Kaprodi',
            'Wadir1',    // Wakil Direktur 1
            'Wadir2',    // Wakil Direktur 2
            'Direktur',
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }
    }
}
