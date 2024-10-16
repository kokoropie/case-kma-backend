<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\ThirdParty\Address;
use Gate;
use Illuminate\Http\Request;
use Validator;

class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('admin');

        $configs = Config::all()->mapWithKeys(function ($config) {
            return [$config->key => $config->value["value"]];
        });

        return response()->json($configs);
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
    public function show(Config $config)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Config $config)
    {
        //
    }

    public function updateAll(Request $request)
    {
        Gate::authorize('admin');

        $data = $request->all();

        $configs = Config::all()->mapWithKeys(function ($config) {
            return [$config->key => $config->value["value"]];
        });

        foreach ($configs as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        $rules = [
            "base_fee" => "required|numeric|min:0",
            "finish_fee" => "required|numeric|min:0",
            "material_fee" => "required|numeric|min:0",
            "from_province" => [
                "required",
                function ($attribute, $value, $fail) {
                    if (Address::provinceDoesntExist($value)) {
                        $fail('The from province is invalid.');
                    }
                }
            ],
            "from_district" => [
                "required",
                function ($attribute, $value, $fail) use ($data) {
                    if (Address::districtDoesntExist($data['from_province'], $value)) {
                        $fail('The from district is invalid.');
                    }
                }
            ]
        ];

        $validated = Validator::validate($data, $rules);

        foreach ($validated as $key => $value) {
            $config = Config::find($key);
            if ($config) {
                $config->value = [
                    "value" => $value
                ];
                if ($config->isDirty("value")) {
                    $config->save();
                }
            }
        }

        return response()->json(Config::all()->mapWithKeys(function ($config) {
            return [$config->key => $config->value["value"]];
        }));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Config $config)
    {
        //
    }
}
