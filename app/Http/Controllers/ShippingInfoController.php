<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\ThirdParty\Address;
use Illuminate\Http\Request;
use Validator;

class ShippingInfoController extends Controller
{
    public function country()
    {
        return response()->json(Address::country());
    }

    public function province()
    {
        return response()->json(Address::province());
    }

    public function district(string $provinceCode)
    {
        return response()->json(Address::district($provinceCode));
    }

    public function cost(Request $request)
    {
        $data = collect($request->all());
        $data->put('CountryCode', str($data->get('CountryCode'))->upper());

        $param = collect(Validator::validate($data->toArray(), [
            "CountryCode" => [
                "required",
                "max:2",
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value != "VN") {
                        $countryList = collect($this->country()->getData());
                        if (!$countryList->contains('code', $value)) {
                            $fail("The country code is invalid.");
                        }
                    }
                },
            ],
            "ToProvince" => [
                "required_if:CountryCode,VN",
                function (string $attribute, mixed $value, \Closure $fail) {
                    $countryList = collect($this->province()->getData());
                    if (!$countryList->contains('code', $value)) {
                        $fail("The to province is invalid.");
                    }
                },
            ],
            "ToDistrict" => [
                "required_if:CountryCode,VN",
                function (string $attribute, mixed $value, \Closure $fail) use ($data) {
                    $countryList = collect($this->district($data["ToProvince"])->getData());
                    if (!$countryList->contains('code', $value)) {
                        $fail("The to district is invalid.");
                    }
                },
            ],
        ]));

        $config = Config::whereIn(
            'key', 
            [
                'from_province',
                'from_district',
            ])
            ->get()
            ->keyBy('key')
            ->map(
                fn($config) => $config->value->toArray()
            )
            ->dot();

        $param->put("FromProvince", $config->get('from_province.value'));
        $param->put("FromDistrict", $config->get('from_district.value'));
        $param->getOrPut("ToProvince", 0);
        $param->getOrPut("ToDistrict", 0);

        return response()->json(Address::cost($param));
    }
}
