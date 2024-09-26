<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use App\ThirdParty\Address;
use Illuminate\Http\Request;
use Validator;

class ShippingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();
        return response()->json($user->shippingAddresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validated = Validator::validate(
            $data,
            [
                'name' => 'required|string',
                'phone_number' => [
                    'required',
                    'string',
                ],
                'address' => [
                    'required',
                    'string'
                ],
                'district' => [
                    'required',
                    function ($attribute, $value, $fail)  use ($data) {
                        if ($data['country'] === 'VN') {
                            if (Address::districtDoesntExist($data['province'], $value)) {
                                $fail('The district is invalid.');
                            }
                        }
                    }
                ],
                'province' => [
                    'required',
                    function ($attribute, $value, $fail)  use ($data) {
                        if ($data['country'] === 'VN') {
                            if (Address::provinceDoesntExist($value)) {
                                $fail('The district is invalid.');
                            }
                        }
                    }
                ],
                'postal_code' => 'required_unless:country,VN',
                'country' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (Address::countryDoesntExist($value)) {
                            $fail('The country is invalid.');
                        }
                    }
                ],
            ]
        );

        if ($validated["country"] == "VN") {
            $newPhone = "+84";
            if (strpos($validated["phone_number"], "0") === 0) {
                $newPhone .= substr($validated["phone_number"], 1);
            } else {
                $newPhone .= $validated["phone_number"];
            }
            $validated["phone_number"] = $newPhone;
            $validated["postal_code"] = '';
        }

        $user = auth('sanctum')->user();
        $shippingAddress = $user->shippingAddresses()->create($validated);

        return response()->json($shippingAddress);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingAddress $shippingAddress)
    {
        return response()->json($shippingAddress);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShippingAddress $shippingAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingAddress $shippingAddress)
    {
        //
    }
}
