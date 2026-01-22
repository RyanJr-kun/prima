<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class CourseDistributionTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['1', 'TI-101', 'Algoritma', 'Budi Santoso', 'Siti Aminah', 'Buku A', 'Paham Logika'],
            ['2', 'TI-102', 'Basis Data', 'Siti Aminah', '', 'Modul B', 'Bisa Query'], // Contoh kosong
        ];
    }

    public function headings(): array
    {
        return [
            'NO',           // Index 0
            'KODE_MATKUL',  // Index 1
            'NAMA_MATKUL',  // Index 2
            'DOSEN_UTAMA',  // Index 3
            'DOSEN_PDDIKTI',// Index 4 <--- KITA SISIPKAN DISINI
            'REFERENSI',    // Index 5
            'LUARAN'        // Index 6
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