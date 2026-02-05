<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'email_verified_at',
        'password',
        'signature_path',
        'nidn',
        'status',
        'profile_photo_path',
        'notification_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'notification_settings' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }
    public function managedProdi()
    {
        return $this->hasOne(Prodi::class, 'kaprodi_id');
    }

    public function teachingDistributions()
    {
        return $this->belongsToMany(CourseDistribution::class, 'course_lecturers')
            ->wherePivot('category', 'real_teaching');
    }
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
    public function workloads()
    {
        return $this->hasMany(Workload::class);
    }
}
