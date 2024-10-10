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
        $return = collect($user->shippingAddresses->toArray());
        $return->transform(function ($address) {
            $address = collect($address);
            $address->forget('user_id');
            if ($address->get('country') === 'VN') {
                $address->put('postal_code', '');
                $address->put('district', Address::district($address->get('province'), $address->get('district')));
                $address->put('province', Address::province($address->get('province')));
            }
            $address->put('country', Address::country($address->get('country')));
            return $address;
        });
        return response()->json($return);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['country'] = strtoupper($data['country'] ?? '');
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
                    function ($attribute, $value, $fail) use ($data) {
                        if ($data['country'] === 'VN') {
                            if (Address::districtDoesntExist($data['province'], $value)) {
                                $fail('The district is invalid.');
                            }
                        }
                    }
                ],
                'province' => [
                    'required',
                    function ($attribute, $value, $fail) use ($data) {
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
            ],
            [
                'phone_number.required' => 'The phone number field is required.',
                'phone_number.string' => 'The phone number must be a string.',
                'address.required' => 'The address field is required.',
                'address.string' => 'The address must be a string.',
                'district.required' => 'The district field is required.',
                'province.required' => 'The province field is required.',
                'postal_code.required_unless' => 'The postal code field is required.',
                'country.required' => 'The country field is required.',
            ]
        );

        $newPhone = Address::country($validated["country"])['dial'];
        if (strpos($validated["phone_number"], "0") === 0) {
            $newPhone .= substr($validated["phone_number"], 1);
        } else {
            $newPhone .= $validated["phone_number"];
        }
        $validated["phone_number"] = $newPhone;

        if ($validated["country"] == "VN") {
            $validated["postal_code"] = '';
        }

        $user = auth('sanctum')->user();

        $shippingAddress = new ShippingAddress();
        $shippingAddress->user_id = $user->user_id;
        $shippingAddress->fill($validated);
        $shippingAddress->save();

        return response()->json($shippingAddress);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingAddress $shippingAddress)
    {
        $address = collect($shippingAddress);
        $address->forget('user_id');
        if ($address->get('country') === 'VN') {
            $address->put('postal_code', '');
            $address->put('district', Address::district($address->get('province'), $address->get('district')));
            $address->put('province', Address::province($address->get('province')));
        }
        $address->put('country', Address::country($address->get('country')));
        return response()->json($address);
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
