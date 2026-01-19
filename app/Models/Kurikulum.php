<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
     protected $fillable = [
        'name',
        'tanggal',
        'prodi_id',
        'semester',
        'is_active',
        'file_path'
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
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
        return $map[$this->semester] ?? $this->semester;
    }

}
