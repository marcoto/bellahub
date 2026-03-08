<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeRecord extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'duration',
        'notes',
    ];

    protected $casts = [
        'date'     => 'date',
        'duration' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and save duration when check_out is set.
     */
    public function calculateDuration(): void
    {
        if ($this->check_in && $this->check_out) {
            $in  = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_in);
            $out = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_out);
            $this->duration = round($out->diffInMinutes($in) / 60, 2);
        }
    }

    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration) {
            return '0h 00m';
        }
        $hours   = (int) floor($this->duration);
        $minutes = (int) round(($this->duration - $hours) * 60);
        return sprintf('%dh %02dm', $hours, $minutes);
    }
}
