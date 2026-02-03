<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = Prodi::pluck('id', 'code');

        $rooms = [
            // KAMPUS 1
            // --- GEDUNG A (TEORI & LAB KOM) ---

            [
                'code' => 'K1-A-L3-R1',
                'name' => 'Ruang Teori A.1',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 3,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP',]
            ],
            [
                'code' => 'K1-A-L3-R2',
                'name' => 'Ruang Teori A.2',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 3,
                'capacity' => 60,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-A-L2-LAB1',
                'name' => 'Lab Komputer 1',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 2,
                'capacity' => 30,
                'type' => 'laboratorium',
                'facility_tags' => ['computer'],
                'for_prodis' => ['TRPL', 'TRO', 'MIK']
            ],
            [
                'code' => 'K1-A-L2-LAB2',
                'name' => 'Lab Komputer 2',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['computer'],
                'for_prodis' => ['TRPL', 'TRO']
            ],
            [
                'code' => 'K1-A-L3-LAB3',
                'name' => 'Lab Komputer 3',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 3,
                'capacity' => 30,
                'type' => 'laboratorium',
                'facility_tags' => ['computer'],
                'for_prodis' => ['TRPL']
            ],
            [
                'code' => 'K1-A-L3-BC',
                'name' => 'Lab Broadcasting',
                'location' => 'kampus_1',
                'building' => 'Gedung A',
                'floor' => 3,
                'capacity' => 30,
                'type' => 'laboratorium',
                'facility_tags' => ['broadcasting'], // Kamera, Green Screen
                'for_prodis' => ['PM']
            ],

            // --- GEDUNG B (Jaringan, Resto BMR/HT) ---

            [
                'code' => 'K1-B-L2-R1',
                'name' => 'Ruang Teori B.1',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 2,
                'capacity' => 35,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-B-L2-R2',
                'name' => 'Ruang Teori B.2',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 2,
                'capacity' => 35,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-B-L2-R3',
                'name' => 'Ruang Teori B.3',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 2,
                'capacity' => 35,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-B-L2-IOT',
                'name' => 'Lab Jaringan & IoT',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 2,
                'capacity' => 30,
                'type' => 'laboratorium',
                'facility_tags' => ['network_iot'],
                'for_prodis' => ['TRPL']
            ],
            [
                'code' => 'K1-B-L1-RESTO',
                'name' => 'Lab Restoran & Bar',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 1,
                'capacity' => 60,
                'type' => 'laboratorium',
                'facility_tags' => ['kitchen_resto'],
                'for_prodis' => ['HT']
            ],
            [
                'code' => 'K1-B-L1-RITEL',
                'name' => 'Lab Simulasi Ritel',
                'location' => 'kampus_1',
                'building' => 'Gedung B',
                'floor' => 1,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['retail_sim'], // Kasir, Display Produk
                'for_prodis' => ['BMR']
            ],


            // --- GEDUNG C (OTOMOTIF) & D ---

            [
                'code' => 'K1-D-L2-R1',
                'name' => 'Ruang Teori D.1',
                'location' => 'kampus_1',
                'building' => 'Gedung D',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-D-L2-R2',
                'name' => 'Ruang Teori D.2',
                'location' => 'kampus_1',
                'building' => 'Gedung D',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-D-L2-R3',
                'name' => 'Ruang Teori D.3',
                'location' => 'kampus_1',
                'building' => 'Gedung D',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['BMR', 'PM', 'TRPL', 'TRO', 'HT', 'AP']
            ],
            [
                'code' => 'K1-C-L1-OTO',
                'name' => 'Bengkel Otomotif',
                'location' => 'kampus_1',
                'building' => 'Gedung C',
                'floor' => 1,
                'capacity' => 40,
                'type' => 'laboratorium',
                'facility_tags' => ['automotive'],
                'for_prodis' => ['TRO']
            ],


            // KAMPUS 2 (FARMASI, TLM, MIK) - Gedung Utama
            // --- FARMASI ---
            [
                'code' => 'K2-L2-KIMIA',
                'name' => 'Lab Kimia (Farmasi)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 50,
                'type' => 'laboratorium',
                'facility_tags' => ['chemistry'], // Lemari Asam, Autoklaf
                'for_prodis' => ['FARM', 'TLM']
            ],
            [
                'code' => 'K2-L2-TEKFAR',
                'name' => 'Lab Teknologi Farmasi',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 50,
                'type' => 'laboratorium',
                'facility_tags' => ['pharmacy_tool', 'chemistry'], // Cetak Tablet, Hardness Tester
                'for_prodis' => ['FARM']
            ],
            [
                'code' => 'K2-L2-BIO',
                'name' => 'Lab Biologi Dasar',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 50,
                'type' => 'laboratorium',
                'facility_tags' => ['microscope'], // Mikroskop, Oven
                'for_prodis' => ['FARM', 'TLM']
            ],
            [
                'code' => 'K2-L1-FARMASET',
                'name' => 'Lab Farmasetika',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 1,
                'capacity' => 35,
                'type' => 'laboratorium',
                'facility_tags' => ['pharmacy_tool'], // Apotek Simulasi, Blender
                'for_prodis' => ['FARM']
            ],
            [
                'code' => 'K2-L1-FARMAKOLOGI',
                'name' => 'Lab Farmakologi',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 1,
                'capacity' => 40,
                'type' => 'laboratorium',
                'facility_tags' => ['pharmacy_tool', 'anatomy_bed'], // Terarium, Bed
                'for_prodis' => ['FARM']
            ],

            // --- TLM (Teknologi Laboratorium Medis) ---
            [
                'code' => 'K2-L2-PATOLOGI',
                'name' => 'Lab Patologi Klinik',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['microscope', 'medkit'], // Hematology Analyzer
                'for_prodis' => ['TLM']
            ],
            [
                'code' => 'K2-L2-SITO',
                'name' => 'Lab Sitohistoteknologi',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['microscope'], // Microtome
                'for_prodis' => ['TLM']
            ],
            [
                'code' => 'K2-L2-BIOMOL',
                'name' => 'Lab Biologi Molekuler',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 20,
                'type' => 'laboratorium',
                'facility_tags' => ['bio_molecular'], // PCR, Biosafety Cabinet
                'for_prodis' => ['TLM']
            ],

            // --- MIK (Manajemen Informasi Kesehatan) ---
            [
                'code' => 'K2-L1-RM-MANUAL',
                'name' => 'Lab Rekam Medis Manual',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 1,
                'capacity' => 20,
                'type' => 'laboratorium',
                'facility_tags' => ['medical_record'], // Rak Berkas
                'for_prodis' => ['MIK']
            ],
            [
                'code' => 'K2-L1-RM-ELEK',
                'name' => 'Lab R.M Elektronik & Statistik',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 1,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['computer', 'medical_record'], // PC + Software RS
                'for_prodis' => ['MIK']
            ],
            [
                'code' => 'K2-L1-KODING',
                'name' => 'Lab Koding (ICD-10)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 1,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['computer', 'medical_record'],
                'for_prodis' => ['MIK']
            ],
            [
                'code' => 'K2-L3-ANATOMI',
                'name' => 'Lab Anatomi',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 25,
                'type' => 'laboratorium',
                'facility_tags' => ['anatomy_bed'], // Manekin, Poster
                'for_prodis' => ['MIK']
            ],
            [
                'code' => 'K2-L3-OSCE',
                'name' => 'Ruang OSCE',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 6,
                'type' => 'laboratorium',
                'facility_tags' => ['anatomy_bed', 'medkit'], // Bed Periksa
                'for_prodis' => ['MIK', 'FARM']
            ],

            // --- RUANG TEORI KAMPUS 2 (SHARED) ---
            [
                'code' => 'K2-L2-R1',
                'name' => 'Ruang Teori 1 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L2-R2',
                'name' => 'Ruang Teori 2 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 2,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L2-R3',
                'name' => 'Ruang Teori 3 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 30,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L2-R4',
                'name' => 'Ruang Teori 4 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 30,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],

            [
                'code' => 'K2-L3-R5',
                'name' => 'Ruang Teori 5 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 50,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L3-R6',
                'name' => 'Ruang Teori 6 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 50,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L3-R7',
                'name' => 'Ruang Teori 7 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L3-R8',
                'name' => 'Ruang Teori 8 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
            [
                'code' => 'K2-L3-R9',
                'name' => 'Ruang Teori 9 (K2)',
                'location' => 'kampus_2',
                'building' => 'Gedung Utama',
                'floor' => 3,
                'capacity' => 25,
                'type' => 'teori',
                'facility_tags' => ['general'],
                'for_prodis' => ['FARM', 'TLM', 'MIK']
            ],
        ];

        foreach ($rooms as $roomData) {
            // Gunakan updateOrCreate agar tidak duplikat saat di-seed ulang
            $room = Room::updateOrCreate(
                ['code' => $roomData['code']], // Kunci pencarian
                [
                    'name'          => $roomData['name'],
                    'location'      => $roomData['location'],
                    'building'      => $roomData['building'],
                    'floor'         => $roomData['floor'],
                    'capacity'      => $roomData['capacity'],
                    'type'          => $roomData['type'],
                    // JSON Array (Cast di model akan otomatis handle ini)
                    'facility_tags' => $roomData['facility_tags'],
                    'is_active'     => true,
                ]
            );

            // Sync Pivot Prodi
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
