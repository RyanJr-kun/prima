<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prodi extends Model
{
    protected $fillable = [
        'code',
        'name',
        'jenjang',
        'lama_studi',
        'kaprodi_id',
        'primary_campus'
    ];

    public function kaprodi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprodi_id');
    }
}
