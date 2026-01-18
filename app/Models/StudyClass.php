<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyClass extends Model
{
    protected $fillable = [
        'academic_period_id', 'name', 'prodi_id', 'kurikulum_id','angkatan', 'semester',
        'total_students', 'academic_advisor_id'
    ];

    public function academicAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }
    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
    public function getFullNameAttribute()
    {
        if ($this->prodi) {
            return $this->prodi->jenjang . ' ' . $this->prodi->code . ' - ' . $this->name;
        }
        return $this->name;
    }
}
