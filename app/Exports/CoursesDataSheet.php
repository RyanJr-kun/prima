<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromArray;

class CoursesDataSheet implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'TEMPLATE MATKUL'; // Nama sheet
    }

    public function array(): array
    {
        // contoh output
        return [
            ['1', 'TRPL101', 'Algoritma', '1', '2', '0', '1', 'Kurikulum 2024', 'general'],
            ['2', 'TRPL102', 'Basis Data', '1', '2', '0', '2', 'Kurikulum 2024', 'computer'],
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE_MATKUL',
            'NAMA_MATKUL',
            'SKS_TEORI',
            'SKS_PRAKTIK',
            'SKS_LAPANGAN',
            'SEMESTER',
            'NAMA_KURIKULUM',
            'FASILITAS (Lihat Tab Sebelah)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
