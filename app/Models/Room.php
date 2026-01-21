<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = [
        'code', 
        'name', 
        'capacity',
        'prodi_ids',
        'type', 
        'location',
        'facility_tag'
        ];

    public function prodis(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'prodi_room');
    }
    
    
    public function getIsGeneralAttribute()
    {
        return $this->prodis()->count() == 0;
    }
}
