<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Prodi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = Prodi::pluck('id', 'code');
        $location = [
            'Kampus 1 - Gd. A - Lantai 1', // 0
            'Kampus 1 - Gd. A - Lantai 2', // 1
            'Kampus 1 - Gd. A - Lantai 3', // 2
            'Kampus 1 - Gd. B - Lantai 1', // 3
            'Kampus 1 - Gd. B - Lantai 2', // 4
            'Kampus 1 - Gd. C - Lantai 1', // 5
            'Kampus 1 - Gd. C - Lantai 2', // 6
            'Kampus 1 - Gd. D - Lantai 1', // 7
            'Kampus 1 - Gd. D - Lantai 2', // 8
            'Kampus 2 - Gd. A - Lantai 1', // 9 
            'Kampus 2 - Gd. A - Lantai 2', // 10
            'Kampus 2 - Gd. A - Lantai 3', // 11
        ];
        $tags =[
            ['general', 'Umum / Standar'],
            ['computer', 'Komputer (PC)'],
            ['network', 'Alat Jaringan & IoT'],
            ['resto', 'Dapur, Resto, & Kamar'],
            ['automotive', 'Bengkel Otomotif']
        ];

        $rooms = [
            [ 
                'code' => 'A-L3-R1',
                'name' => 'Ruang A.1',
                'capacity' => 25,
                'type' => 'teori',
                'location' => $location[2],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0],
            ],
            [
                'code' => 'A-L3-R2',
                'name' => 'Ruang A.2',
                'capacity' => 60,
                'type' => 'teori',
                'location' => $location[2],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0],

            ],
            
            // --- GEDUNG A (LAB KOMPUTER) ---
            [
                'code' => 'LAB-KOM-1',
                'name' => 'Lab Komputer 1',
                'capacity' => 30,
                'type' => 'laboratorium',
                'location' => $location[1],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[1],
            ],
            [
                'code' => 'LAB-KOM-2',
                'name' => 'Lab Komputer 2',
                'capacity' => 25,
                'type' => 'laboratorium',
                'location' => $location[1],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'], 
                'facility_tag' => $tags[1],
            ],
            [
                'code' => 'LAB-KOM-3',
                'name' => 'Lab Komputer 3',
                'capacity' => 30,
                'type' => 'laboratorium',
                'location' => $location[2],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[1],
            ],

            // --- GEDUNG B (TEORI) ---
            [
                'code' => 'B-L2-R1',
                'name' => 'Ruang B.1',
                'capacity' => 35,
                'type' => 'teori',
                'location' => $location[4],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0],
            ],
            [
                'code' => 'B-L2-R2',
                'name' => 'Ruang B.2',
                'capacity' => 35,
                'type' => 'teori',
                'location' => $location[4],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0]
            ],
            [
                'code' => 'B-L2-R3',
                'name' => 'Ruang B.3',
                'capacity' => 50,
                'type' => 'teori',
                'location' => $location[4],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0]
            ],

            // --- GEDUNG B (LAB KHUSUS) ---
            [
                'code' => 'LAB-RESTO',
                'name' => 'Lab Restoran & Tata Hidang',
                'capacity' => 30,
                'type' => 'laboratorium', 
                'location' => $location[3],
                'for_prodis' => ['HT'],
                'facility_tag' => $tags[3]
            ],
            [
                'code' => 'LAB-IOT',
                'name' => 'Lab Jaringan & IoT',
                'capacity' => 30,
                'type' => 'laboratorium',
                'location' => $location[4],
                'for_prodis' => ['TRPL'],
                'facility_tag' => $tags[2]

            ], 

            // --- GEDUNG C & D ---
            [
                'code' => 'LAB-OTO',
                'name' => 'Bengkel Otomotif',
                'capacity' => 40,
                'type' => 'laboratorium',
                'location' => $location[5],
                'for_prodis' => ['TRO'],
                'facility_tag' => $tags[4]
            ],
            [
                'code' => 'D-L2-R1',
                'name' => 'Ruang D.1',
                'capacity' => 25,
                'type' => 'teori',
                'location' => $location[8],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0]
            ],
            [
                'code' => 'D-L2-R2',
                'name' => 'Ruang D.2',
                'capacity' => 25,
                'type' => 'teori',
                'location' => $location[8],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0]
            ],
            [
                'code' => 'D-L2-R3',
                'name' => 'Ruang D.3',
                'capacity' => 25,
                'type' => 'teori',
                'location' => $location[8],
                'for_prodis' => ['TRPL', 'TRO', 'HT', 'BMR', 'PM', 'AP'],
                'facility_tag' => $tags[0],
            ],

            // Kampus 2 Ged.A
            [
                'code' => 'LAB-AK',
                'name' => 'Lab. Audit Klinis',
                'capacity' => 30,
                'type' => 'laboratorium',
                'location' => $location[9],
                'for_prodis' => [],
                'facility_tag' => $tags[0], 
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = Room::firstOrCreate(
                ['code' => $roomData['code']],
                [
                    'name' => $roomData['name'],
                    'capacity' => $roomData['capacity'],
                    'type' => $roomData['type'],
                    'location' => $roomData['location'],
                    'facility_tag' => $roomData['facility_tag'][0],
                ]
            );

            if (!empty($roomData['for_prodis'])) {
                $prodiIds = [];
                foreach ($roomData['for_prodis'] as $prodiCode) {
                    if (isset($prodis[$prodiCode])) {
                        $prodiIds[] = $prodis[$prodiCode];
                    }
                }
                $room->prodis()->sync($prodiIds);
            }
        }
    }
}

// R1
// R2


// R5 
// R6
// R7
// R8
// R9
// Lab. Audit Klinis 
// Lab. Anatomi Fisiologi
// Lab. Koding & Rembuirsement
// Lab. Statistik &  Pelaporan
// Lab. Patologi Klinik 
// Lab. Biologi Molekuler 
// Lab. Sitohistoteknologi 
// Lantai 1 - Lab Farmasetika
// Lantai 1 - Lab Farmakologi
// Lantai 2 - Lab Kimia
// Lantai 2 - Lab Bilogi
// Lantai 2 - Lab. Teknologi Farmasi
// Lantai 3 - Lab. Komputer

// Prodi Farmasi
// Lantai 1 - Lab Farmasetika
// Lantai 1 - Lab Farmakologi
// Lantai 2 - Lab. Teknologi Farmasi 
// Lantai 2 - Lab Kimia
// Lantai 2 - Lab Bilogi
// R1
// R2
// R5 
// R9

// Prodi TLM
// Lab. Patologi Klinik 
// Lab. Biologi Molekuler 
// Lab. Sitohistoteknologi
// R7
// R8

// Prodi MIK
// R6
// R7
// Lab. Audit Klinis 
// Lab. Anatomi Fisiologi
// Lab. Komputer = lt 3
// Lab. Koding & Rembuirsement
// Lab. Statistik &  Pelaporan