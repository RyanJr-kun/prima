<?php

namespace Database\Seeders;

use App\Models\TimeSlots;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
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

        $slotsJumat = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4

            ['13:00', '13:50'], // 5
            ['13:50', '14:40'], // 6
            ['14:40', '15:30'], // 7
            ['15:30', '16:20'], // 8
        ];
        $this->insertSlots($slotsJumat, 'pagi', 'jumat');

        $slotsKaryawanWeekday = [
            ['16:20', '17:10'], // 5
            ['17:10', '18:00'], // 6
            ['18:00', '18:50'], // 7
            ['18:50', '19:40'], // 8
            ['19:40', '20:30'], // 9
        ];

        $this->insertSlots($slotsKaryawanWeekday, 'malam', 'senin_jumat');

        $slotsPagiSabtu = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            ['11:20', '12:10'], // 5

            ['13:00', '13:50'], // 6
        ];

        $this->insertSlots($slotsPagiSabtu, 'pagi', 'sabtu');

        $slotsKaryawanSabtu = [
            ['08:00', '08:50'], // 1
            ['08:50', '09:40'], // 2
            ['09:40', '10:30'], // 3
            ['10:30', '11:20'], // 4
            ['11:20', '12:10'], // 5

            ['13:00', '13:50'], // 7
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
                'day_group'  => $dayGroup,
            ]);
        }
    }
}
