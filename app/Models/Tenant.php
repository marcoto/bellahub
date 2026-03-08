<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'city',
            'address',
            'logo',
            'primary_color',
            'secondary_color',
            'plan',
            'status',
            'admin_name',
        ];
    }

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'city',
        'address',
        'logo',
        'primary_color',
        'secondary_color',
        'plan',
        'status',
        'admin_name',
    ];

    protected $attributes = [
        'primary_color'   => '#8b5cf6',
        'secondary_color' => '#6d28d9',
        'plan'            => 'basic',
        'status'          => 'active',
    ];

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan) {
            'professional' => 'Profesional',
            'enterprise'   => 'Enterprise',
            default        => 'Básico',
        };
    }
}
