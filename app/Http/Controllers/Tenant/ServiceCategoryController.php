<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        ServiceCategory::create($data);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $serviceCategory->update($data);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        // Detach from services before deleting
        $serviceCategory->services()->detach();
        $serviceCategory->delete();

        return back()->with('success', 'Categoría eliminada correctamente.');
    }
}
