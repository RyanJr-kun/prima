<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Kurikulum;
use App\Models\StudyClass;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudyClassImport implements ToCollection, WithStartRow
{
    protected $periodId;

    public function __construct($periodId)
    {
        $this->periodId = $periodId;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Gunakan null coalescing (??) untuk mencegah error undefined index
                $namaKelas  = trim($row[1] ?? '');
                $kodeProdi  = strtoupper(trim($row[2] ?? ''));
                $angkatan   = trim($row[3] ?? '');
                $total_students = (int) ($row[4] ?? 0);
                $semester   = (int) ($row[5] ?? 1);

                // Validasi Shift (Default Pagi jika typo/kosong)
                $shiftRaw   = strtolower(trim($row[6] ?? 'pagi'));
                $shift      = in_array($shiftRaw, ['pagi', 'malam']) ? $shiftRaw : 'pagi';

                $kurikulumName = trim($row[7] ?? '');
                $advisorName   = trim($row[8] ?? '');

                if (empty($namaKelas) || empty($kodeProdi)) continue;

                $prodi = Prodi::where('code', $kodeProdi)->first();

                if (!$prodi) {
                    \Log::warning("Prodi tidak ditemukan untuk kode: $kodeProdi di baris " . ($index + 2));
                    continue;
                }

                // Logika Kurikulum: Cari Nama -> Cari Default Aktif -> Skip jika gagal
                $kurikulumId = null;
                if (!empty($kurikulumName)) {
                    $kurikulum = Kurikulum::where('name', 'LIKE', '%' . $kurikulumName . '%')->first();
                    if ($kurikulum) $kurikulumId = $kurikulum->id;
                }

                if (!$kurikulumId) {
                    // Fallback: Ambil kurikulum aktif terakhir di prodi tersebut
                    $defaultKurikulum = Kurikulum::where('prodi_id', $prodi->id)->where('is_active', true)->latest()->first();
                    if ($defaultKurikulum) {
                        $kurikulumId = $defaultKurikulum->id;
                    } else {
                        \Log::warning("Skip Baris " . ($index + 2) . ": Kurikulum tidak valid dan tidak ada default.");
                        continue; // Wajib ada karena constraint database
                    }
                }

                $advisorId = null;
                if (!empty($advisorName)) {
                    $advisor = User::role('dosen')->where('name', 'LIKE', '%' . $advisorName . '%')->first();
                    if (!$advisor) {
                        $cleanName = preg_replace('/(Dr\.|Ir\.|Prof\.|S\.Kom|M\.Kom|S\.T|M\.T|Ph\.D)/i', '', $advisorName);
                        $cleanName = trim($cleanName);
                        if (!empty($cleanName)) {
                            $advisor = User::role('dosen')->where('name', 'LIKE', '%' . $cleanName . '%')->first();
                        }
                    }
                    if ($advisor) $advisorId = $advisor->id;
                }

                StudyClass::updateOrCreate(
                    [
                        'name' => $namaKelas,
                        'academic_period_id' => $this->periodId,
                        'prodi_id' => $prodi->id,
                        'angkatan' => $angkatan,
                        'semester' => $semester,
                        'shift' => $shift,
                    ],
                    [
                        'total_students' => $total_students,
                        'kurikulum_id' => $kurikulumId,
                        'academic_advisor_id' => $advisorId,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Error Import StudyClass Row " . ($index + 2) . ": " . $e->getMessage());
                continue;
            }
        }
    }
}
