<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sks_teori',
        'sks_praktik',
        'sks_lapangan',
        'semester',
        'kurikulum_id',
        'prodi_id'
    ];

    /**
     * Accessor untuk mengambil Total SKS (JML) secara otomatis.
     * Cara panggil: $course->sks_total
     */
    protected function sksTotal(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
                ($attributes['sks_teori'] ?? 0) +
                ($attributes['sks_praktik'] ?? 0) +
                ($attributes['sks_lapangan'] ?? 0)
        );
    }
    // app/Models/Course.php
    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }
}
