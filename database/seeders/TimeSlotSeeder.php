<?php

namespace Database\Seeders;

use App\Models\TimeSlots;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================================
        // 1. KELAS REGULER (PAGI)
        // ==========================================================

        // A. Senin - Kamis (08:00 - 16:00, Istirahat 12:00-13:00)
        $slotsReguler = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            ['11:20', '12:10'], // 5
            // Istirahat
            ['13:00', '13:50'], // 6
            ['13:50', '14:40'], // 7
            ['14:40', '15:30'], // 8
            ['15:30', '16:20'], // 9
        ];
        $this->insertSlots($slotsReguler, 'pagi', 'senin_kamis');

        // B. Jumat (08:00 - 16:00, Istirahat Jumatan 11:30-13:00)
        $slotsJumat = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            // Istirahat Jumatan
            ['13:00', '13:50'], // 5
            ['13:50', '14:40'], // 6
            ['14:40', '15:30'], // 7
            ['15:30', '16:20'], // 8
        ];
        $this->insertSlots($slotsJumat, 'pagi', 'jumat');


        // ==========================================================
        // 2. KELAS KARYAWAN ("MALAM") - LOGIKA HYBRID
        // ==========================================================

        // C. Senin - Jumat (13:00 - 20:00)
        // Logika: 13-17 (50 menit), 17-20 (30 menit)
        $slotsKaryawanWeekday = [
            // --- BLOK SIANG (50 Menit) ---
            ['13:00', '13:50'], // 1
            ['13:50', '14:40'], // 2
            ['14:40', '15:30'], // 3
            ['15:30', '16:20'], // 4
            // Transisi (40 Menit menyesuaikan cut-off jam 17:00 di PDF)
            ['16:20', '17:00'], // 5 

            // --- BLOK MALAM (30 Menit - Padat) ---
            ['17:00', '17:30'], // 6
            ['17:30', '18:00'], // 7
            ['18:00', '18:30'], // 8
            ['18:30', '19:00'], // 9
            ['19:00', '19:30'], // 10
            ['19:30', '20:00'], // 11
        ];
        // Kita beri label 'malam' agar masuk kategori shift malam/karyawan
        // Tapi day_group 'malam_senin_jumat' untuk membedakan pola jamnya
        $this->insertSlots($slotsKaryawanWeekday, 'malam', 'senin_jumat');

        $slotsPagiSabtu = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            ['11:20', '12:10'], // 5
            // Istirahat 12:10 - 13:00
            ['13:00', '13:50'], // 6
        ];
        // Perhatikan parameter ke-2 adalah 'pagi'
        $this->insertSlots($slotsPagiSabtu, 'pagi', 'sabtu');


        // D. Sabtu (08:00 - 15:30) - Full Day 50 Menit
        $slotsKaryawanSabtu = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            ['11:20', '12:10'], // 5
            ['12:10', '13:00'], // 6
            ['13:00', '13:50'], // 7
            ['13:50', '14:40'], // 8
            ['14:40', '15:30'], // 9
        ];
        $this->insertSlots($slotsKaryawanSabtu, 'malam', 'sabtu');
    }

    private function insertSlots(array $times, string $shift, string $dayGroup)
    {
        foreach ($times as $index => $time) {
            TimeSlots::create([
                'name'       => 'Sesi ' . ($index + 1),
                'start_time' => $time[0],
                'end_time'   => $time[1],
                'shift'      => $shift,
                'day_group'  => $dayGroup, // Kunci utama pembeda jadwal
            ]);
        }
    }
}
