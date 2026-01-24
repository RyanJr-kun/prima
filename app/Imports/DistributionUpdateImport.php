<?php

namespace App\Imports;

use App\Models\CourseDistribution;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // PENTING: Pakai Heading Row

class DistributionUpdateImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ambil ID Distribusi (Kunci Utama)
            // Pastikan penulisan key array sesuai dengan header excel (lowercase & snake_case)
            // Header 'ID_DISTRIBUSI (JANGAN DIUBAH)' akan terbaca 'id_distribusi_jangan_diubah'

            // Cara aman ambil ID (karena header bisa panjang): Ambil index pertama jika array numerik, 
            // tapi karena WithHeadingRow, kita harus sesuaikan dengan slugnya.
            // Lebih aman kita cek dulu rownya.

            $idDistribusi = $row['id_distribusi_jangan_diubah'] ?? null;
            if (!$idDistribusi) continue;

            $namaDosenUtama = trim($row['dosen_utama'] ?? '');
            $namaDosenTeam  = trim($row['dosen_pddikti'] ?? '');

            // Cari ID Dosen berdasarkan Nama
            $dosenUtamaId = null;
            if ($namaDosenUtama) {
                $user = User::where('name', 'LIKE', "%$namaDosenUtama%")->first();
                if ($user) $dosenUtamaId = $user->id;
            }

            $dosenTeamId = null;
            if ($namaDosenTeam) {
                $user = User::where('name', 'LIKE', "%$namaDosenTeam%")->first();
                if ($user) $dosenTeamId = $user->id;
            }

            // EKSEKUSI UPDATE
            // Langsung tembak ID-nya, ga perlu cari matkul/kelas lagi. Cepat & Akurat.
            $distribusi = CourseDistribution::find($idDistribusi);

            if ($distribusi) {
                $distribusi->update([
                    'user_id'         => $dosenUtamaId,
                    'pddikti_user_id' => $dosenTeamId,
                    'referensi'       => $row['referensi'] ?? null,
                    'luaran'          => $row['luaran'] ?? null,
                ]);
            }
        }
    }
}
