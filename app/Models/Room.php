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

    const TAG_LABELS = [
        // UMUM
        'general'        => 'Umum (AC, Proyektor)',

        // IT & TEKNIK (Biru)
        'computer'       => 'Lab Komputer',
        'network_iot'    => 'Jaringan & IoT',
        'automotive'     => 'Mesin & Otomotif',
        'broadcasting'   => 'Broadcasting',

        // BISNIS (Ungu)
        'retail_sim'     => 'Simulasi Ritel',
        'kitchen_resto'  => 'Kitchen & Resto',

        // KESEHATAN (Hijau)
        'medical_record' => 'Rekam Medis',
        'microscope'     => 'Mikroskop/Bio',
        'chemistry'      => 'Lab Kimia',
        'bio_molecular'  => 'Bio Molekuler',
        'pharmacy_tool'  => 'Alat Farmasi',
        'anatomy_bed'    => 'Anatomi/Bed',
    ];

    /**
     * Helper untuk mendapatkan Label Warna Badge
     */
    public static function getTagColor($key)
    {
        $colors = [
            'general'        => 'secondary', // Abu-abu

            'computer'       => 'info',      // Biru Muda
            'network_iot'    => 'info',
            'automotive'     => 'warning',   // Kuning/Oranye
            'broadcasting'   => 'warning',

            'retail_sim'     => 'primary',   // Ungu/Biru Tua
            'kitchen_resto'  => 'danger',    // Merah

            // Default Kesehatan = Hijau
            'medical_record' => 'success',
            'microscope'     => 'success',
            'chemistry'      => 'success',
            'bio_molecular'  => 'success',
            'pharmacy_tool'  => 'success',
            'anatomy_bed'    => 'success',
        ];

        return $colors[$key] ?? 'dark'; // Default hitam jika tidak dikenal
    }

    /**
     * Helper ambil nama text
     */
    public static function getTagName($key)
    {
        return self::TAG_LABELS[$key] ?? ucfirst($key);
    }
}
