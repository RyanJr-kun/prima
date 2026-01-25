<?php

namespace App\Exports;

use App\Models\CourseDistribution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Event\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class DistributionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $periodId;
    protected $prodiId;
    protected $semester;
    protected $totalRows = 0;

    public function __construct($periodId, $prodiId = null, $semester = null)
    {
        $this->periodId = $periodId;
        $this->prodiId = $prodiId;
        $this->semester = $semester;
    }

    public function collection()
    {
        $query = CourseDistribution::with([
            'studyClass.prodi',
            'course',
            'teachingLecturers',
            'pddiktiLecturers'
        ])
            ->where('academic_period_id', $this->periodId);

        if ($this->prodiId) {
            $query->whereHas('studyClass', function ($q) {
                $q->where('prodi_id', $this->prodiId);
            });
        }

        if ($this->semester) {
            $query->whereHas('studyClass', function ($q) {
                $q->where('semester', $this->semester);
            });
        }

        $data = $query->get()->sortBy(function ($item) {
            return $item->studyClass->prodi->name . '-' .
                $item->studyClass->semester . '-' .
                $item->studyClass->name . '-' .
                $item->course->name;
        });

        $this->totalRows = $data->count();

        return $data;
    }

    public function headings(): array
    {
        return [
            'ID_DISTRIBUSI (JANGAN DIUBAH)',
            'PROGRAM STUDI',
            'SEMESTER',
            'NAMA_KELAS',
            'KODE_MATKUL',
            'NAMA_MATKUL',
            'SKS_TEORI',
            'SKS_PRAKTIK',
            'SKS_LAP',
            'SKS_TOTAL',
            'DOSEN_UTAMA',
            'DOSEN_PDDIKTI',
            'REFERENSI',
            'LUARAN',
        ];
    }

    public function map($row): array
    {
        $dosenReal = $row->teachingLecturers->pluck('name')->implode(', ');
        $dosenPddikti = $row->pddiktiLecturers->pluck('name')->implode(', ');

        return [
            $row->id,
            $row->studyClass->prodi->name ?? '-',
            $row->studyClass->semester ?? '-',
            $row->studyClass->name,
            $row->course->code,
            $row->course->name,

            $row->course->sks_teori ?? 0,
            $row->course->sks_praktik ?? 0,
            $row->course->sks_lapangan ?? 0,
            $row->course->sks_total ?? 0,

            $dosenReal,
            $dosenPddikti,
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rows = $this->totalRows + 1; // +1 karena ada Header
                $footerRow = $rows + 1; // Baris untuk TOTAL

                // Jika tidak ada data, jangan bikin footer
                if ($this->totalRows == 0) return;

                // Tulis Label "TOTAL"
                $sheet->setCellValue('F' . $footerRow, 'TOTAL KESELURUHAN:');

                // Rumus Excel SUM untuk SKS (Kolom G, H, I, J)
                // Contoh: =SUM(G2:G100)
                $sheet->setCellValue('G' . $footerRow, "=SUM(G2:G{$rows})"); // Total Teori
                $sheet->setCellValue('H' . $footerRow, "=SUM(H2:H{$rows})"); // Total Praktik
                $sheet->setCellValue('I' . $footerRow, "=SUM(I2:I{$rows})"); // Total Lapangan
                $sheet->setCellValue('J' . $footerRow, "=SUM(J2:J{$rows})"); // Total SKS Semua

                // Styling Footer (Bold & Background Abu-abu)
                $sheet->getStyle("F{$footerRow}:J{$footerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFEFEF'],
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
                    ]
                ]);
            },
        ];
    }
}
