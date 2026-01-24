<?php

namespace App\Exports;

use App\Models\CourseDistribution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistributionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $periodId;
    protected $prodiId;
    protected $semester;

    // 1. UPDATE CONSTRUCTOR: Menerima 3 parameter untuk filter
    public function __construct($periodId, $prodiId = null, $semester = null)
    {
        $this->periodId = $periodId;
        $this->prodiId = $prodiId;
        $this->semester = $semester;
    }

    public function collection()
    {
        // 2. QUERY DENGAN FILTER
        $query = CourseDistribution::with(['studyClass.prodi', 'course', 'user', 'pddiktiUser'])
            ->where('academic_period_id', $this->periodId);

        // Filter Prodi (Jika user memilih prodi di filter)
        if ($this->prodiId) {
            $query->whereHas('studyClass', function ($q) {
                $q->where('prodi_id', $this->prodiId);
            });
        }

        // Filter Semester (Jika user memilih semester)
        if ($this->semester) {
            $query->whereHas('studyClass', function ($q) {
                $q->where('semester', $this->semester);
            });
        }

        // 3. SORTING YANG LEBIH RAPI
        // Urutkan: Nama Prodi -> Semester -> Nama Kelas -> Nama Matkul
        return $query->get()->sortBy(function ($item) {
            return $item->studyClass->prodi->name . '-' .
                $item->studyClass->semester . '-' .
                $item->studyClass->name . '-' .
                $item->course->name;
        });
    }

    public function headings(): array
    {
        return [
            'ID_DISTRIBUSI (JANGAN DIUBAH)', // Kolom A (Hidden ID)
            'PROGRAM STUDI',                 // Tambahan info biar jelas
            'SEMESTER',                      // Tambahan info biar jelas
            'NAMA_KELAS',
            'KODE_MATKUL',
            'NAMA_MATKUL',
            'SKS',                           // Tambahan info SKS
            'DOSEN_UTAMA',
            'DOSEN_PDDIKTI',
            'REFERENSI',                     // Dari kode lama Anda
            'LUARAN',                        // Dari kode lama Anda
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->studyClass->prodi->name ?? '-',
            $row->studyClass->semester ?? '-',
            $row->studyClass->name,
            $row->course->code,
            $row->course->name,
            $row->course->sks_total ?? 0, // Mengambilaccessor SKS Total
            $row->user->name ?? '',
            $row->pddiktiUser->name ?? '', // PERBAIKAN: Menggunakan relasi yang benar (pddiktiUser)
            $row->referensi,
            $row->luaran,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
