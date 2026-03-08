<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    protected $attributes = [
        'color' => '#8b5cf6',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_category', 'service_category_id', 'service_id');
    }
}
