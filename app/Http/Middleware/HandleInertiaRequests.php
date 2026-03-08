<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebar_collapsed' => $request->session()->get('sidebar_collapsed', false),
            'tenant' => function () {
                if (!function_exists('tenancy') || !tenancy()->initialized) {
                    return null;
                }
                $logo = tenant('logo');
                return [
                    'name'            => tenant('name'),
                    'logo'            => $logo ? asset('storage/' . $logo) : null,
                    'primary_color'   => tenant('primary_color') ?? '#8b5cf6',
                    'secondary_color' => tenant('secondary_color') ?? '#6d28d9',
                    'plan'            => tenant('plan') ?? 'basic',
                    'status'          => tenant('status') ?? 'active',
                ];
            },
        ];
    }
}
