<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
     protected $fillable = [
        'name',
        'tanggal',
        'prodi_id',
        'is_active',
        'file_path'
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

}
