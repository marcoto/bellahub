<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $services = Service::with(['categories', 'workers'])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => $this->mapService($s));

        $categories = ServiceCategory::orderBy('name')->get();

        $workers = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'role']);

        return Inertia::render('Services/Index', [
            'services'   => $services,
            'categories' => $categories,
            'workers'    => $workers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'duration'      => 'required|integer|min:1|max:480',
            'price'         => 'required|numeric|min:0',
            'is_active'     => 'boolean',
            'category_ids'  => 'array',
            'category_ids.*'=> 'integer|exists:service_categories,id',
            'worker_ids'    => 'array',
            'worker_ids.*'  => 'integer|exists:users,id',
        ]);

        $service = Service::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'duration'    => $data['duration'],
            'price'       => $data['price'],
            'is_active'   => $data['is_active'] ?? true,
        ]);

        if (!empty($data['category_ids'])) {
            $service->categories()->sync($data['category_ids']);
        }

        if (!empty($data['worker_ids'])) {
            $service->workers()->sync($data['worker_ids']);
        }

        return back()->with('success', 'Servicio creado correctamente.');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'duration'      => 'required|integer|min:1|max:480',
            'price'         => 'required|numeric|min:0',
            'is_active'     => 'boolean',
            'category_ids'  => 'array',
            'category_ids.*'=> 'integer|exists:service_categories,id',
            'worker_ids'    => 'array',
            'worker_ids.*'  => 'integer|exists:users,id',
        ]);

        $service->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'duration'    => $data['duration'],
            'price'       => $data['price'],
            'is_active'   => $data['is_active'] ?? true,
        ]);

        $service->categories()->sync($data['category_ids'] ?? []);
        $service->workers()->sync($data['worker_ids'] ?? []);

        return back()->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service)
    {
        $service->categories()->detach();
        $service->workers()->detach();
        $service->delete();

        return back()->with('success', 'Servicio eliminado correctamente.');
    }

    private function mapService(Service $service): array
    {
        return [
            'id'             => $service->id,
            'name'           => $service->name,
            'description'    => $service->description,
            'duration'       => $service->duration,
            'duration_label' => $service->duration_label,
            'price'          => (float) $service->price,
            'is_active'      => $service->is_active,
            'categories'     => $service->categories->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'color' => $c->color,
            ]),
            'workers'        => $service->workers->map(fn($w) => [
                'id'    => $w->id,
                'name'  => $w->name,
                'color' => $w->color,
            ]),
        ];
    }
}
