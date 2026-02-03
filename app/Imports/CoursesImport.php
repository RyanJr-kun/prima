<?php

namespace App\Imports;

use App\Models\Course;
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

        // 1. Mapping Header Excel
        $kodeMatkul    = trim($row['kode_matkul'] ?? '');
        $namaMatkul    = trim($row['nama_matkul'] ?? '');
        $namaKurikulum = trim($row['nama_kurikulum'] ?? '');

        // Skip jika data utama kosong
        if (empty($kodeMatkul) || empty($namaKurikulum)) {
            return;
        }

        // 2. Cari Kurikulum (Case Insensitive Search)
        $kurikulum = Kurikulum::where('name', 'LIKE', '%' . $namaKurikulum . '%')->first();

        if (!$kurikulum) {
            // Log error tapi jangan stop proses import row lain
            \Log::warning("Baris $rowIndex Skipped: Kurikulum '$namaKurikulum' tidak ditemukan.");
            return;
        }

        // 3. LOGIC PARSING TAGS (PENTING!)
        // Excel Input: "computer, network" -> PHP Array: ['computer', 'network']
        $rawTags = $row['fasilitas_lihat_tab_sebelah'] ?? $row['fasilitas'] ?? '';
        $tagsArray = [];

        if (!empty($rawTags)) {
            // Pecah berdasarkan koma, lalu trim spasi
            $tagsArray = array_map('trim', explode(',', $rawTags));
        } else {
            // Default jika kosong
            $tagsArray = ['general'];
        }

        // 4. Update or Create
        Course::updateOrCreate(
            [
                'code'         => $kodeMatkul,
                'kurikulum_id' => $kurikulum->id,
            ],
            [
                'name'          => $namaMatkul,
                'semester'      => $row['semester'] ?? 1,
                'sks_teori'     => $row['sks_teori'] ?? 0,
                'sks_praktik'   => $row['sks_praktik'] ?? 0,
                'sks_lapangan'  => $row['sks_lapangan'] ?? 0,
                'required_tags' => $tagsArray,
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
