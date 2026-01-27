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
        'required_tags'
    ];

    protected $casts = [
        'required_tags' => 'array',
    ];

    protected function sksTotal(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => ($attributes['sks_teori'] ?? 0) +
                ($attributes['sks_praktik'] ?? 0) +
                ($attributes['sks_lapangan'] ?? 0)
        );
    }

    public function prodi()
    {
        return $this->hasOneThrough(
            Prodi::class,
            kurikulum::class,
            'id',
            'id',
            'kurikulum_id',
            'prodi_id'
        );
    }

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function getProdiAttribute()
    {
        return $this->kurikulum->prodi ?? null;
    }
}
