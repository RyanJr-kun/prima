<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyClass extends Model
{
    protected $fillable = [
        'academic_period_id', 
        'name', 
        'prodi_id', 
        'kurikulum_id',
        'angkatan', 
        'semester',
        'total_students', 
        'academic_advisor_id',
        'shift',
    ];

    public function academicAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }
    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
    public function getFullNameAttribute()
    {
        $tahunDuaDigit = substr($this->angkatan, -2);
        $kodeProdi = $this->prodi->code ?? 'PRODI';
        $jenjang = $this->prodi->jenjang ?? '';
        return "{$jenjang} {$kodeProdi} {$tahunDuaDigit}{$this->name}";
    }
    
    public function getSemesterRomawiAttribute()
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII'
        ];

        // Kembalikan romawi jika ada, jika tidak kembalikan angka aslinya
        return $map[$this->semester] ?? $this->semester;
    }
}
