<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromArray;


class CoursesTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['1', 'TRPL101', 'Algoritma', '1', '2', '1', '1', 'perangkat lunak', 'pc'],
            ['2', 'TRPL102', 'Basis Data', '2', '3', '0', '2', 'perangkat lunak', 'pc'],
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
            'FASILITAS'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bikin Header jadi Tebal (Bold)
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
