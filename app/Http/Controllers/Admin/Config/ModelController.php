<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\PhoneModel;
use Gate;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('admin');

        $models = PhoneModel::all();

        return response()->json($models);
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
    public function show(PhoneModel $model)
    {
        Gate::authorize('admin');

        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhoneModel $model)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhoneModel $model)
    {
        //
    }
}
