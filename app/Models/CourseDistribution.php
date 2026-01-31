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
            get: fn(mixed $value, array $attributes) => ($attributes['sks_teori'] ?? 0) +
                ($attributes['sks_praktik'] ?? 0) +
                ($attributes['sks_lapangan'] ?? 0)
        );
    }

    public function studyClass()
    {
        return $this->belongsTo(StudyClass::class, 'study_class_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }


    public function teachingLecturers()
    {
        return $this->belongsToMany(User::class, 'course_lecturers')
            ->wherePivot('category', 'real_teaching')
            ->withTimestamps();
    }

    public function pddiktiLecturers()
    {
        return $this->belongsToMany(User::class, 'course_lecturers')
            ->wherePivot('category', 'pddikti_reporting')
            ->withTimestamps();
    }

    public function allLecturers()
    {
        return $this->belongsToMany(User::class, 'course_lecturers')
            ->withPivot('category')
            ->withTimestamps();
    }

    public function schedule()
    {
        // Kita asumsikan 1 Distribusi = 1 Jadwal Utama
        return $this->hasOne(Schedule::class, 'course_id', 'course_id')
            ->whereColumn('study_class_id', 'course_distributions.study_class_id');
    }
}
