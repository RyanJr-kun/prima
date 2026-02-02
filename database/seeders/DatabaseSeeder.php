<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProdiSeeder::class,
            KurikulumSeeder::class,
            RoomSeeder::class,
            TimeSlotSeeder::class,
        ]);

        \App\Models\AcademicPeriod::create([
            'name' => 'Ganjil 2025/2026',
            'is_active' => true,
        ]);
    }
}
