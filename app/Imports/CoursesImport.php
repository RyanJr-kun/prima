<?php

namespace App\Imports;

use App\Models\CourseDistribution;
use App\Models\Course;
use App\Models\User;
use App\Models\StudyClass;
use App\Models\Kurikulum;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CoursesImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        // Mapping data dari Heading Excel (otomatis jadi lowercase & snake_case)
        // Contoh: 'KODE_MATKUL' di excel menjadi 'kode_matkul' di array
        $kodeMatkul    = trim($row['kode_matkul'] ?? '');
        $namaMatkul    = trim($row['nama_matkul'] ?? '');
        $namaKurikulum = trim($row['nama_kurikulum'] ?? '');

        if (empty($kodeMatkul) || empty($namaKurikulum)) {
            return; // Skip baris kosong
        }

        $kurikulum = Kurikulum::where('name', 'LIKE', '%' . $namaKurikulum . '%')->first();

        if (!$kurikulum) {
            \Log::error("Baris $rowIndex Gagal: Kurikulum '$namaKurikulum' tidak ditemukan di database.");
            return;
        }

        Course::updateOrCreate(
            [
                'code' => $kodeMatkul,
                'kurikulum_id' => $kurikulum->id,
            ],
            [
                'name' => $namaMatkul,
                'semester' => $row['semester'] ?? 1,
                'sks_teori' => $row['sks_teori'] ?? 0,
                'sks_praktik' => $row['sks_praktik'] ?? 0,
                'sks_lapangan' => $row['sks_lapangan'] ?? 0,
                'required_tag' => $row['fasilitas'] ?? null,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000; // Proses per 1000 baris untuk hemat memori
    }
}
