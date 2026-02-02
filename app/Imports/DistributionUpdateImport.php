<?php

namespace App\Imports;

use App\Models\CourseDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistributionUpdateImport implements ToCollection, WithHeadingRow
{
    private function findUserIds($nameString)
    {
        if (!$nameString) return [];
        $names = explode(';', $nameString);
        $ids = [];

        foreach ($names as $nameRaw) {
            $cleanName = trim($nameRaw);
            if (empty($cleanName)) continue;

            $user = null;
            if (preg_match('/\(ID:(\d+)\)/', $cleanName, $matches)) {
                $userId = $matches[1];
                $user = User::find($userId);

                if ($user) {
                    $ids[] = $user->id;
                }

                continue;
            }

            $nameOnly = trim(preg_replace('/\(ID:.*?\)/', '', $cleanName));

            if (strlen($nameOnly) < 3) continue;

            $user = User::where('name', $nameOnly)->first();

            if (!$user) {
                $user = User::where('name', 'LIKE', "{$nameOnly}%")->first();
            }

            if ($user) {
                $ids[] = $user->id;
            }
        }

        return array_unique($ids);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Ambil String ID (Contoh: "501;502;503")
            $idString = $row['id_distribusi_gabungan'] ?? $row['id_distribusi_jangan_diubah'] ?? null;
            if (!$idString) continue;

            $distribusiIds = explode(';', $idString);

            // 2. Cari ID Dosen (CUKUP SEKALI SAJA PER BARIS EXCEL)
            // Logikanya: Dosen yang tertulis di baris ini berlaku untuk SEMUA ID Distribusi tersebut.
            $teachingIds = $this->findUserIds($row['dosen_utama'] ?? '');
            $pddiktiIds  = $this->findUserIds($row['dosen_pddikti'] ?? '');

            $now = now();

            // 3. Loop ke setiap Kelas (ID Distribusi)
            foreach ($distribusiIds as $idDistribusi) {
                $idDistribusi = trim($idDistribusi);
                if (empty($idDistribusi)) continue;

                $distribusi = CourseDistribution::find($idDistribusi);
                if (!$distribusi) continue;

                // --- A. Update Data Text (Referensi & Luaran) ---
                $distribusi->update([
                    'referensi' => $row['referensi'],
                    'luaran'    => $row['luaran'],
                ]);

                // --- B. Reset Pivot Table (Hapus Dosen Lama) ---
                DB::table('course_lecturers')
                    ->where('course_distribution_id', $idDistribusi)
                    ->delete();

                // --- C. Siapkan Data Insert Baru ---
                $pivotData = [];

                // C.1 Masukkan Dosen Pengajar (Real Teaching)
                foreach ($teachingIds as $uid) {
                    $pivotData[] = [
                        'course_distribution_id' => $idDistribusi,
                        'user_id'    => $uid,
                        'category'   => 'real_teaching',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // C.2 Masukkan Dosen PDDIKTI (Cek Duplikat)
                foreach ($pddiktiIds as $uid) {
                    // Cek apakah user ini sudah dimasukkan sebagai pddikti_reporting di iterasi ini?
                    // (Note: Cek terhadap array $pivotData saat ini)
                    $isDuplicate = false;
                    foreach ($pivotData as $existing) {
                        if ($existing['user_id'] == $uid && $existing['category'] == 'pddikti_reporting') {
                            $isDuplicate = true;
                            break;
                        }
                    }

                    if (!$isDuplicate) {
                        $pivotData[] = [
                            'course_distribution_id' => $idDistribusi,
                            'user_id'    => $uid,
                            'category'   => 'pddikti_reporting',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                // --- D. Eksekusi Insert ke Database ---
                if (!empty($pivotData)) {
                    DB::table('course_lecturers')->insert($pivotData);
                }
            }
        }
    }
}
