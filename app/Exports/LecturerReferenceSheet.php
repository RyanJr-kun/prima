<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;

class LecturerReferenceSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithMapping
{
    public function title(): string
    {
        return 'DAFTAR KODE DOSEN (REF)';
    }

    public function collection()
    {
        return User::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'NAMA DOSEN (Salin kolom ini)',
            'NAMA ASLI',
            'ID SYSTEM',
            'EMAIL'
        ];
    }

    public function map($user): array
    {
        return [
            "{$user->name} (ID:{$user->id})",
            $user->name,
            $user->id,
            $user->email
        ];
    }
}
