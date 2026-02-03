<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\CourseDistribution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DistributionUpdateImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $users;

    public function __construct()
    {
        // 1. OPTIMASI USER: Load semua user ke Memory (RAM)
        // Jadi kita tidak perlu query DB ribuan kali saat mencari nama dosen.
        $this->users = User::select('id', 'name')->get();
    }

    private function findUserIds($nameString)
    {
        if (!$nameString) return [];
        $names = explode(';', $nameString);
        $ids = [];

        foreach ($names as $nameRaw) {
            $cleanName = trim($nameRaw);
            if (empty($cleanName)) continue;

            $foundUser = null;

            // A. Cek ID via Regex (Paling Cepat & Akurat)
            if (preg_match('/\(ID:(\d+)\)/', $cleanName, $matches)) {
                $userId = (int)$matches[1];
                // Cari di Collection Memory
                $foundUser = $this->users->firstWhere('id', $userId);

                if ($foundUser) {
                    $ids[] = $foundUser->id;
                    continue; // Skip pencarian nama jika ID ketemu
                }
            }

            // B. Cek Nama (Fallback)
            $nameOnly = trim(preg_replace('/\(ID:.*?\)/', '', $cleanName));
            if (strlen($nameOnly) < 3) continue;

            // Cari Exact Match di Memory (Case Insensitive)
            $foundUser = $this->users->first(function ($u) use ($nameOnly) {
                return strcasecmp($u->name, $nameOnly) === 0;
            });

            // Cari Like Match (Starts With) di Memory
            if (!$foundUser) {
                $foundUser = $this->users->first(function ($u) use ($nameOnly) {
                    return Str::startsWith(strtolower($u->name), strtolower($nameOnly));
                });
            }

            if ($foundUser) {
                $ids[] = $foundUser->id;
            }
        }

        return array_unique($ids);
    }

    public function collection(Collection $rows)
    {
        // Kumpulkan data batch
        $allDistribusiIds = [];
        $updatesText = [];
        $pivotDataBatch = [];

        $now = now();

        // --- STEP 1: PARSING DATA (Tanpa Query DB Berat) ---
        foreach ($rows as $row) {
            $idString = $row['id_distribusi_gabungan'] ?? $row['id_distribusi_jangan_diubah'] ?? null;
            if (!$idString) continue;

            $distribusiIds = explode(';', $idString);

            // Cari ID Dosen sekali saja per baris
            $teachingIds = $this->findUserIds($row['dosen_utama'] ?? '');
            $pddiktiIds  = $this->findUserIds($row['dosen_pddikti'] ?? '');

            foreach ($distribusiIds as $idDistribusi) {
                $idDistribusi = trim($idDistribusi);
                if (empty($idDistribusi)) continue;

                $allDistribusiIds[] = $idDistribusi;

                // Simpan data update text untuk diproses nanti
                $updatesText[$idDistribusi] = [
                    'referensi' => $row['referensi'],
                    'luaran'    => $row['luaran'],
                ];

                // Siapkan Data Insert Pivot (Memory)
                // Real Teaching
                foreach ($teachingIds as $uid) {
                    $pivotDataBatch[] = [
                        'course_distribution_id' => $idDistribusi,
                        'user_id'    => $uid,
                        'category'   => 'real_teaching',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Pddikti (Check Duplicate simple di array memory lokal)
                foreach ($pddiktiIds as $uid) {
                    // Cek manual apakah kombinasi user+kategori ini sudah ada di batch ini?
                    // (Opsional: Bisa dilewati jika yakin data bersih, tapi aman dicek)
                    $exists = false;
                    // Logic check sederhana: User PDDIKTI boleh masuk asalkan beda kategori
                    $pivotDataBatch[] = [
                        'course_distribution_id' => $idDistribusi,
                        'user_id'    => $uid,
                        'category'   => 'pddikti_reporting',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (empty($allDistribusiIds)) return;

        // --- STEP 2: EKSEKUSI DATABASE (BATCH / BULK) ---

        DB::transaction(function () use ($allDistribusiIds, $updatesText, $pivotDataBatch) {
            // 1. UPDATE TEXT (Referensi/Luaran)
            // Sayangnya Eloquent tidak punya batch update native yang mudah untuk text beda-beda.
            // Kita loop update ringan (hanya update text, tidak relasi).
            // Tapi kita filter ID dulu biar valid.
            $validDistributions = CourseDistribution::whereIn('id', $allDistribusiIds)->get();

            foreach ($validDistributions as $dist) {
                if (isset($updatesText[$dist->id])) {
                    $dist->update($updatesText[$dist->id]);
                }
            }

            // 2. DELETE PIVOT LAMA (SEKALIGUS 1 QUERY)
            // "Hapus semua dosen untuk semua ID Distribusi yang ada di file Excel ini"
            DB::table('course_lecturers')
                ->whereIn('course_distribution_id', $allDistribusiIds)
                ->delete();

            // 3. INSERT PIVOT BARU (SEKALIGUS 1 QUERY / CHUNK)
            // Insert ribuan row sekaligus jauh lebih cepat
            if (!empty($pivotDataBatch)) {
                // Pecah jadi chunk kecil (misal 500) biar SQL tidak error "Placeholder limit"
                foreach (array_chunk($pivotDataBatch, 500) as $chunk) {
                    DB::table('course_lecturers')->insert($chunk);
                }
            }
        });
    }

    // Wajib ada agar memory tidak jebol jika file Excel sangat besar
    public function chunkSize(): int
    {
        return 500; // Proses per 500 baris Excel
    }
}
