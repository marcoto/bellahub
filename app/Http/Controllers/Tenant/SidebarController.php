<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    public function toggle(Request $request): RedirectResponse
    {
        session(['sidebar_collapsed' => $request->boolean('collapsed')]);

        return back();
    }
}
