<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = [
        'code',
        'name',
        'location',
        'building',
        'floor',
        'facility_tags',
        'prodi_ids',
        'capacity',
        'type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'floor' => 'integer',
        'facility_tags' => 'array',
        'capacity' => 'integer'
    ];

    public function prodis(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'prodi_room');
    }

    public function getFullNameAttribute()
    {
        // Output: "Kampus 1 - Gedung A - Lt.2 - R.Teori 1 (Kaps: 40)"
        return ucfirst(str_replace('_', ' ', $this->location)) . ' - ' .
            $this->building . ' - Lt.' .
            $this->floor . ' - ' .
            $this->name . ' (' . $this->capacity . ')';
    }

    public function getIsGeneralAttribute()
    {
        return $this->prodis()->count() == 0;
    }

    public function isAvailableForProdi($prodiId)
    {
        if ($this->is_general) {
            return true;
        }

        return $this->prodis()->where('prodis.id', $prodiId)->exists();
    }
}
