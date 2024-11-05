<?php

namespace App\Http\Controllers;

use App\Models\CaseColor;
use App\Models\Configuration;
use App\Models\PhoneModel;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class ConfigurationController extends Controller
{
    public function index()
    {
        //
    }

    private function listOptions()
    {
        $config = Config::whereIn(
            'key', 
            [
                'base_fee',
                'material_fee',
                'finish_fee',
            ])
            ->get()
            ->keyBy('key')
            ->map(
                fn($config) => $config->value->toArray()
            )
            ->dot();
        $base = $config->get('base_fee.value');
        $color = CaseColor::all()->loadCount(['orders'])->sortByDesc('orders_count')->values();

        $model = PhoneModel::all();
        $material = [
            [
                'slug' => 'silicone',
                'name' => 'Silicone',
                'price' => 0
            ], 
            [
                'slug' => 'polycarbonate',
                'name' => 'Soft Polycarbonate',
                'description' => 'Scratch-resistant coating',
                'price' => $config->get('material_fee.value')
            ]
        ];
        $finish = [
            [
                'slug' => 'smooth',
                'name' => 'Smooth',
                'price' => 0
            ], 
            [
                'slug' => 'textured',
                'name' => 'Textured',
                'description' => 'Soft grippy texture',
                'price' => $config->get('finish_fee.value')
            ]
        ];

        return [
            'base' => $base,
            'colors' => $color,
            'models' => $model,
            'materials' => $material,
            'finishes' => $finish,
        ];
    }
    
    public function create()
    {
        return response()->json($this->listOptions());
    }

    public function store(Request $request)
    {
        $data = collect($request->all());
        $lowerValueWithKey = ["color", "model", "material", "finish"];
        $data->transform(function ($value, $key) use ($lowerValueWithKey) {
            if (in_array($key, $lowerValueWithKey)) {
                return strtolower($value);
            }
            return $value;
        });

        $validated = Validator::validate($data->toArray(), [
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
                    if (!in_array($value, ['silicone', 'polycarbonate'])) {
                        $fail("The $attribute must be either 'Silicone' or 'Polycarbonate'.");
                    }
                }
            ],
            'finish' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
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

        $config = Config::whereIn(
            'key', 
            [
                'base_fee',
                'material_fee',
                'finish_fee',
            ])
            ->get()
            ->keyBy('key')
            ->map(
                fn($config) => $config->value->toArray()
            )
            ->dot();

        $data['amount'] = $config->get('base_fee.value');
        $data['amount_material'] = $validated['material'] === 'silicone' ? 0 : $config->get('material_fee.value');
        $data['amount_finish'] = $validated['finish'] === 'smooth' ? 0 : $config->get('finish_fee.value');

        unset($data['image']);
        unset($data['croppedImage']);

        $user = $request->user('sanctum');
        $configuration = $user->configurations()->create($data);

        return response()->json($configuration->load(['model', 'color']));
    }

    public function show(Configuration $configuration)
    {
        return response()->json($configuration->load(['model', 'color']));
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
