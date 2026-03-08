<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
        'duration'  => 'integer',
    ];

    protected $attributes = [
        'duration'  => 30,
        'price'     => 0.00,
        'is_active' => true,
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_category', 'service_id', 'service_category_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_user', 'service_id', 'user_id');
    }

    public function getDurationLabelAttribute(): string
    {
        $h = intdiv($this->duration, 60);
        $m = $this->duration % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0) return "{$h}h";
        return "{$m}min";
    }
}
