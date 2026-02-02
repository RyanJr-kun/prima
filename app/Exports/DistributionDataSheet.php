<?php

namespace App\Exports;

use App\Models\CourseDistribution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Event\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistributionDataSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $periodId;
    protected $prodiId;
    protected $semester;

    public function __construct($periodId, $prodiId, $semester)
    {
        $this->periodId = $periodId;
        $this->prodiId = $prodiId;
        $this->semester = $semester;
    }

    public function title(): string
    {
        return 'DATA DISTRIBUSI';
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
            $query->whereHas('studyClass', fn($q) => $q->where('prodi_id', $this->prodiId));
        }

        if ($this->semester) {
            $query->whereHas('studyClass', fn($q) => $q->where('semester', $this->semester));
        }

        $allData = $query->get();

        $grouped = $allData->groupBy(function ($item) {
            return $item->course_id . '-' . $item->studyClass->shift . '-' . $item->studyClass->prodi_id;
        });

        return $grouped->map(function ($group) {
            $firstItem = $group->first();
            $firstItem->merged_ids = $group->pluck('id')->implode(';');
            $firstItem->merged_class_names = $group->pluck('studyClass.name')->implode(' ; ');
            $firstItem->total_students_sum = $group->sum('studyClass.total_students');

            return $firstItem;
        });
    }

    public function headings(): array
    {
        return [
            'ID_DISTRIBUSI (GABUNGAN)', // Judul Kolom berubah
            'SHIFT',                    // Tambah info shift biar jelas
            'DAFTAR KELAS',
            'PROGRAM STUDI',
            'SEMESTER',
            'KODE_MATKUL',
            'NAMA_MATKUL',
            'SKS_TOTAL',
            'DOSEN_UTAMA',
            'DOSEN_PDDIKTI',
            'REFERENSI',
            'LUARAN',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
    public function map($row): array
    {
        $dosenReal = $row->teachingLecturers->map(fn($u) => "{$u->name} (ID:{$u->id})")->implode('; ');
        $dosenPddikti = $row->pddiktiLecturers->map(fn($u) => "{$u->name} (ID:{$u->id})")->implode('; ');

        return [
            $row->merged_ids,
            ucfirst($row->studyClass->shift),
            $row->merged_class_names,
            $row->studyClass->prodi->name ?? '-',
            $row->studyClass->semester ?? '-',
            $row->course->code,
            $row->course->name,
            $row->course->sks_total ?? 0,
            $dosenReal,
            $dosenPddikti,
            $row->referensi,
            $row->luaran,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $rows = $this->totalRows + 1; // Baris terakhir data (+1 Header)
                $footerRow = $rows + 1;       // Baris Footer

                if ($this->totalRows == 0) return;

                // 1. Tulis Label "TOTAL KESELURUHAN" di Kolom G (Nama Matkul)
                // Supaya rata kanan dengan angka SKS
                $sheet->setCellValue('G' . $footerRow, 'TOTAL SKS:');
                $sheet->getStyle('G' . $footerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                // 2. Rumus Sum hanya untuk Kolom H (SKS TOTAL)
                // Karena di format baru kita cuma menampilkan SKS Total (Teori/Praktek di-hide biar ringkas)
                $sheet->setCellValue('H' . $footerRow, "=SUM(H2:H{$rows})");

                // 3. Styling Footer
                $sheet->getStyle("G{$footerRow}:H{$footerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFEFEF'], // Abu-abu muda
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ]
                ]);
            },
        ];
    }
}
