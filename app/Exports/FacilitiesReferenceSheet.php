<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FacilitiesReferenceSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle, WithStyles
{
    public function title(): string
    {
        return 'KODE FASILITAS (REF)'; // nama sheet
    }

    public function headings(): array
    {
        return [
            'KODE (Salin kolom ini)',
            'KETERANGAN / DESKRIPSI'
        ];
    }

    public function array(): array
    {
        // list fasilitas
        $tags = [
            'general'        => 'Umum (AC, Proyektor, Board)',
            'computer'       => 'Komputer (PC / Lab Kom)',
            'network_iot'    => 'Jaringan, Sensor & IoT',
            'automotive'     => 'Mesin & Otomotif',
            'broadcasting'   => 'Studio, Kamera & Audio',
            'retail_sim'     => 'Simulasi Ritel & Kasir',
            'kitchen_resto'  => 'Dapur, Bar & Resto',
            'medical_record' => 'Rekam Medis (Rak/Berkas)',
            'microscope'     => 'Mikroskop & Biologi',
            'chemistry'      => 'Kimia & Lemari Asam',
            'bio_molecular'  => 'PCR & Molekuler',
            'pharmacy_tool'  => 'Alat Farmasi & Cetak Tablet',
            'anatomy_bed'    => 'Anatomi & Bed Pasien',
        ];

        // Ubah format Array menjadi baris Excel
        $rows = [];
        foreach ($tags as $key => $desc) {
            $rows[] = [$key, $desc];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],

            ],
            'A' => [
                'font' => ['bold' => true, 'color' => ['rgb' => '0000FF']],
            ]
        ];
    }
}
