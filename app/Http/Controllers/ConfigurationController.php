<?php

namespace App\Http\Controllers;

use App\Models\CaseColor;
use App\Models\Configuration;
use App\Models\PhoneModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfigurationController extends Controller
{
    public function index()
    {
        //
    }
    
    public function create()
    {
        $color = CaseColor::all();
        $model = PhoneModel::all();
        $material = ['Silicone', 'Polycarbonate'];
        $finish = ['Smooth', 'Textured'];

        return response()->json([
            'colors' => $color,
            'models' => $model,
            'materials' => $material,
            'finishes' => $finish,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'croppedImage' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $regex = '/^data:image\/(jpeg|png|jpg);base64,/';
                    if (!preg_match($regex, $value)) {
                        $fail("The $attribute must be a base64 encoded image.");
                    }
                }
            ],
            'height' => 'required|numeric|min:1',
            'width' => 'required|numeric|min:1',
            'color' => [
                'required',
                Rule::exists(CaseColor::class, 'slug')
            ],
            'model' => [
                'required',
                Rule::exists(PhoneModel::class, 'slug')
            ],
            'material' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $value = strtolower($value);
                    if (!in_array($value, ['silicone', 'polycarbonate'])) {
                        $fail("The $attribute must be either 'Silicone' or 'Polycarbonate'.");
                    }
                }
            ],
            'finish' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $value = strtolower($value);
                    if (!in_array($value, ['smooth', 'textured'])) {
                        $fail("The $attribute must be either 'Smooth' or 'Textured'.");
                    }
                }
            ],
        ]);

        $publicId = time();
        $date = date('Y/m/d');
        $folder = "case-kma/{$date}";

        $image = cloudinary()->upload($validated['image'], [
            'folder' => $folder,
            'public_id' => "raw_" . $publicId,
        ])->getSecurePath();
        $cropped_image = cloudinary()->upload($validated['croppedImage'], [
            'folder' => $folder,
            'public_id' => "cropped_" . $publicId,
        ])->getSecurePath();

        $data = array_merge($validated, [
            'image_url' => $image,
            'cropped_image_url' => $cropped_image,
        ]);

        unset($data['image']);
        unset($data['croppedImage']);

        $user = $request->user('sanctum');
        $configuration = $user->configurations()->create($data);

        return response()->json($configuration->load(['model', 'color']));
    }

    public function show(Configuration $configuration)
    {
        //
    }

    public function edit(Configuration $configuration)
    {
        return response()->json($configuration->load(['model', 'color']));
    }

    public function update(Request $request, Configuration $configuration)
    {
        // 
    }

    public function destroy(Configuration $configuration)
    {
        //
    }
}
