<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CourseDistribution extends Model
{
    protected $fillable = [
        'academic_period_id',
        'study_class_id', 
        'course_id',
        'user_id',
        'pddikti_user_id',
        'referensi',
        'luaran',      
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected function sksTotal(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
                ($attributes['sks_teori'] ?? 0) +
                ($attributes['sks_praktik'] ?? 0) +
                ($attributes['sks_lapangan'] ?? 0)
        );
    }

    public function studyClass() 
    {
        return $this->belongsTo(StudyClass::class, 'study_class_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }
    

    public function teamTeaching(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pddikti_user_id');
    }
    
}