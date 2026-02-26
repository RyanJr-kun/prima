<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DistributionExport implements WithMultipleSheets
{
    protected $periodId;
    protected $prodiId;
    protected $semester;

    public function __construct($periodId, $prodiId = null, $semester = null)
    {
        $this->periodId = $periodId;
        $this->prodiId = $prodiId;
        $this->semester = $semester;
    }

    public function sheets(): array
    {
        return [
            // Sheet 1: Data Distribusi (Yang mau diedit)
            new DistributionDataSheet($this->periodId, $this->prodiId, $this->semester),

            // Sheet 2: Referensi Data Dosen
            new LecturerReferenceSheet(),
        ];
    }
}
