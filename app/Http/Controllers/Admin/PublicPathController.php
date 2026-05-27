<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PublicPathRegistry;
use Illuminate\Http\Request;

class PublicPathController extends Controller
{
    public function check(Request $request, PublicPathRegistry $paths)
    {
        $request->validate([
            'path' => 'required|string|max:255',
            'company_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'menu_id' => 'nullable|integer',
        ]);

        $except = array_filter([
            'company_id' => $request->input('company_id'),
            'user_id' => $request->input('user_id'),
            'menu_id' => $request->input('menu_id'),
        ]);

        $path = $paths->normalizePath($request->input('path'));
        $available = $paths->isPathAvailable($path, $except);

        return response()->json([
            'path' => $path,
            'available' => $available,
            'message' => $available ? null : 'Esa URL ya está en uso.',
        ]);
    }
}
