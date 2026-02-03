<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CoursesTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            // Sheet 1: Template Kosong (Input Data)
            new CoursesDataSheet(),

            // Sheet 2: Contekan Tags Fasilitas
            new FacilitiesReferenceSheet(),
        ];
    }
}
