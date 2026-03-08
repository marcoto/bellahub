<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'color',
        'hire_date',
        'vacation_days',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'hire_date'         => 'date',
        'is_active'         => 'boolean',
        'vacation_days'     => 'integer',
    ];

    protected $attributes = [
        'role'          => 'worker',
        'color'         => '#8b5cf6',
        'vacation_days' => 22,
        'is_active'     => true,
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'worker_id');
    }

    public function timeRecords(): HasMany
    {
        return $this->hasMany(TimeRecord::class);
    }

    public function vacationRequests(): HasMany
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function getHoursTodayAttribute(): float
    {
        return (float) $this->timeRecords()
            ->whereDate('date', today())
            ->whereNotNull('check_out')
            ->sum('duration');
    }

    public function getHoursYearAttribute(): float
    {
        return (float) $this->timeRecords()
            ->whereYear('date', now()->year)
            ->whereNotNull('check_out')
            ->sum('duration');
    }

    public function getVacationUsedAttribute(): int
    {
        return (int) $this->vacationRequests()
            ->where('status', 'approved')
            ->whereYear('from_date', now()->year)
            ->sum('days');
    }

    public function getIsClockedInAttribute(): bool
    {
        return $this->timeRecords()
            ->whereDate('date', today())
            ->whereNull('check_out')
            ->exists();
    }
}
