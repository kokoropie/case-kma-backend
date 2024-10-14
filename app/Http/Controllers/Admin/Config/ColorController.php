<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\CaseColor;
use Gate;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('admin');

        $colors = CaseColor::all();

        return response()->json($colors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CaseColor $color)
    {
        Gate::authorize('admin');

        return response()->json($color);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CaseColor $color)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CaseColor $color)
    {
        //
    }
}
