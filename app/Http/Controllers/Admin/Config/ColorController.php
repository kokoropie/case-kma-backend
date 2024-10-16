<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\CaseColor;
use Gate;
use Illuminate\Http\Request;
use Str;

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
        Gate::authorize('admin');

        $validated = $request->validate([
            "name" => "required|string",
            "hex_code" => "required|hex_color"
        ]);

        $color = new CaseColor;
        $color->name = $validated["name"];
        do 
        {
            $slug = Str::slug($color->name);
        } while (CaseColor::find($slug));
        $color->slug = $slug;
        $color->hex_code = $validated["hex_code"];
        $color->save();

        return response()->json($color);
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
        Gate::authorize('admin');

        $validated = $request->validate([
            "name" => "required|string",
            "hex_code" => "required|hex_color"
        ]);

        $color->name = $validated["name"];
        $color->hex_code = $validated["hex_code"];
        $color->save();

        return response()->json($color);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CaseColor $color)
    {
        Gate::authorize('admin');

        $color->delete();

        return response()->json(CaseColor::all());
    }
}
