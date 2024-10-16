<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\Controller;
use App\Models\PhoneModel;
use Gate;
use Illuminate\Http\Request;
use Str;

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
        Gate::authorize('admin');

        $validated = $request->validate([
            "name" => "required|string",
            'image' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $regex = '/^data:image\/(jpeg|png|jpg);base64,/';
                    if (!preg_match($regex, $value)) {
                        $fail("The $attribute must be a base64 encoded image.");
                    }
                }
            ],
        ]);

        $model = new PhoneModel();
        $model->name = $validated["name"];
        do 
        {
            $slug = Str::slug($model->name);
        } while (PhoneModel::find($slug));
        $model->slug = $slug;
        $publicId = time();
        $folder = "case-kma/model/{$slug}";

        $image = cloudinary()->upload($validated['image'], [
            'folder' => $folder,
            'public_id' => $publicId,
        ])->getSecurePath();
        $model->image_url = $image;
        $model->save();

        return response()->json($model);
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
        Gate::authorize('admin');

        $validated = $request->validate([
            "name" => "required|string",
            'image' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $regex = '/^data:image\/(jpeg|png|jpg);base64,/';
                    if (!preg_match($regex, $value)) {
                        $fail("The $attribute must be a base64 encoded image.");
                    }
                }
            ],
        ]);

        $slug = $model->slug;
        $model->name = $validated["name"];

        if (!empty($validated['image'])) {
            $folder = "case-kma/model/{$slug}";
            
            $old_publicId = Str::of($model->image_url)->explode("{$folder}/")->last();
            cloudinary()->destroy("{$folder}/$old_publicId");
            
            $publicId = time();

            $image = cloudinary()->upload($validated['image'], [
                'folder' => $folder,
                'public_id' => $publicId,
            ])->getSecurePath();
            $model->image_url = $image;
        }

        if ($model->isDirty())
            $model->save();

        return response()->json($model);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhoneModel $model)
    {
        Gate::authorize('admin');

        $model->delete();

        return response()->json(PhoneModel::all());
    }
}
