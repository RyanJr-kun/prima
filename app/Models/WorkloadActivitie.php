<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkloadActivitie extends Model
{
    protected $table = 'workload_activities';

    protected $fillable = [
        'workload_id',
        'category',
        'activity_name',
        'sks_load',
        'sks_real',
        'realisasi_pertemuan',
        'jenis_ujian',
        'description',
        'document_path'
    ];

    public function workload()
    {
        return $this->belongsTo(Workload::class);
    }
}
