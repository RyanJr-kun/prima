<?php

namespace App\Imports;

use App\Models\CourseDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // PENTING: Pakai Heading Row

class DistributionUpdateImport implements ToCollection, WithHeadingRow
{
    private function findUserIds($nameString)
    {
        if (!$nameString) return [];
        $names = explode(';', $nameString);
        $ids = [];

        foreach ($names as $name) {
            $cleanName = trim($name);
            if (empty($cleanName)) continue;

            // 1. Coba cari match dengan string utuh (siapa tahu di DB namanya pakai gelar)
            $user = User::where('name', 'LIKE', "%{$cleanName}%")->first();

            // 2. Jika tidak ketemu dan ada koma (kemungkinan format "Nama, Gelar"), ambil nama depan saja
            if (!$user && str_contains($cleanName, ',')) {
                $parts = explode(',', $cleanName);
                $nameOnly = trim($parts[0]);
                if (!empty($nameOnly)) {
                    $user = User::where('name', 'LIKE', "%{$nameOnly}%")->first();
                }
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
            $idDistribusi = $row['id_distribusi_jangan_diubah'] ?? null;
            if (!$idDistribusi) continue;

            $distribusi = CourseDistribution::find($idDistribusi);
            if (!$distribusi) continue;

            $teachingIds = $this->findUserIds($row['dosen_utama'] ?? '');
            $pddiktiIds  = $this->findUserIds($row['dosen_pddikti'] ?? '');

            $coordinatorId = $teachingIds[0] ?? $distribusi->user_id;

            $distribusi->update([
                'user_id'   => $coordinatorId,
                'referensi' => $row['referensi'] ?? $distribusi->referensi,
                'luaran'    => $row['luaran'] ?? $distribusi->luaran,
            ]);

            DB::table('course_lecturers')
                ->where('course_distribution_id', $idDistribusi)
                ->delete();

            $pivotData = [];
            $now = now();

            foreach ($teachingIds as $uid) {
                $pivotData[] = [
                    'course_distribution_id' => $idDistribusi,
                    'user_id'  => $uid,
                    'category' => 'real_teaching',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($pddiktiIds as $uid) {
                $pivotData[] = [
                    'course_distribution_id' => $idDistribusi,
                    'user_id'  => $uid,
                    'category' => 'pddikti_reporting',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($pivotData)) {
                DB::table('course_lecturers')->insertOrIgnore($pivotData);
            }
        }
    }
}
