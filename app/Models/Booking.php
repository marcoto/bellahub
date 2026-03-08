<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'client_name',
        'client_phone',
        'date',
        'time_start',
        'time_end',
        'worker_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_service');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
            default     => $this->status,
        };
    }
}
