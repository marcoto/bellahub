<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationRequest extends Model
{
    protected $fillable = [
        'user_id',
        'from_date',
        'to_date',
        'days',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'days'      => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pendiente',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            default    => $this->status,
        };
    }
}
