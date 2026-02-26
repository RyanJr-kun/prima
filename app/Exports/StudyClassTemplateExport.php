<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromArray;

class StudyClassTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            // contoh output
            ['1', 'A', 'TRPL', '2024', '30', '1', 'pagi', 'software', 'Wachid'],
            ['2', 'B', 'TRPL', '2024', '28', '1', 'pagi', 'software', 'Canggih'],
            ['3', 'A', 'TRPL', '2023', '32', '3', 'malam', 'software', 'Dwi'],
            ['4', 'B', 'TRPL', '2023', '25', '3', 'malam', 'software', 'Susena'],
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA_KELAS',
            'KODE_PRODI',
            'ANGKATAN',
            'TOTAL_MAHASISWA',
            'SEMESTER',
            'SHIFT',
            'KURIKULUM',
            'PEMBIMBING_AKADEMIK',
        ];
    }

    public function styles(Worksheet $sheet)
    {

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
