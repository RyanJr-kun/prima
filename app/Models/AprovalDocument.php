<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AprovalDocument extends Model
{
    protected $table = 'aproval_documents';

    protected $fillable = [
        'academic_period_id',
        'prodi_id',
        'campus',
        'shift',
        'type',
        'status',
        'feedback_message',
        'action_by_user_id',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function lastActionUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by_user_id');
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['type']) {
                    'distribusi_matkul'  => 'Distribusi Mata Kuliah',
                    'jadwal_perkuliahan' => 'Jadwal Perkuliahan',
                    'beban_kerja_dosen'  => 'Laporan Beban Kerja Dosen',
                    'kalender_akademik'  => 'Kalender Akademik',
                    default              => ucfirst($attributes['type']),
                };
            }
        );
    }

    /**
     * Mengubah status database menjadi Teks yang mudah dibaca user
     * Cara Pakai di Blade: {{ $doc->status_text }}
     */
    protected function statusText(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status']) {
                    'draft'             => 'Draft (Belum Diajukan)',
                    'submitted'         => 'Menunggu Review Kaprodi',
                    'approved_kaprodi'  => 'Menunggu Review Wadir 1',
                    'approved_wadir1'   => 'Menunggu Review Wadir 2',
                    'approved_wadir2'   => 'Menunggu Pengesahan Direktur',
                    'approved_direktur' => 'FINAL / DISETUJUI',
                    'rejected'          => 'DIKEMBALIKAN (REVISI)',
                    default             => 'Status Tidak Dikenal',
                };
            }
        );
    }

    protected function statusPendek(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status']) {
                    'draft'             => 'Draft (Belum Diajukan)',
                    'submitted'         => 'Menunggu Review Wadir 1',
                    'approved_wadir1'   => 'Menunggu Pengesahan Direktur',
                    'approved_direktur' => 'FINAL / DISETUJUI',
                    'rejected'          => 'DIKEMBALIKAN (REVISI)',
                    default             => 'Status Tidak Dikenal',
                };
            }
        );
    }

    /**
     * Menentukan warna badge Bootstrap berdasarkan status
     * Cara Pakai di Blade: <span class="badge bg-{{ $doc->status_color }}">
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                return match ($attributes['status']) {
                    'draft'             => 'secondary',
                    'submitted'         => 'warning',
                    'approved_kaprodi'  => 'info',
                    'approved_wadir1'   => 'primary',
                    'approved_wadir2'   => 'dark',
                    'approved_direktur' => 'success',
                    'rejected'          => 'danger',
                    default             => 'light',
                };
            }
        );
    }
}
