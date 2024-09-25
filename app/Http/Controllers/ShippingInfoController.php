<?php

namespace App\Http\Controllers;

use App\ThirdParty\Address;
use App\ThirdParty\Currency\Currency;
use Http;
use Illuminate\Http\Request;
use Str;
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
            "FromProvince" => [
                "required_if:CountryCode,VN",
                function (string $attribute, mixed $value, \Closure $fail) {
                    $countryList = collect($this->province()->getData());
                    if (!$countryList->contains('code', $value)) {
                        $fail("The from province is invalid.");
                    }
                },
            ],
            "FromDistrict" => [
                "required_if:CountryCode,VN",
                function (string $attribute, mixed $value, \Closure $fail) use ($data) {
                    $countryList = collect($this->district($data["FromProvince"])->getData());
                    if (!$countryList->contains('code', $value)) {
                        $fail("The from district is invalid.");
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

        $param->getOrPut("FromProvince", 0);
        $param->getOrPut("FromDistrict", 0);
        $param->getOrPut("ToProvince", 0);
        $param->getOrPut("ToDistrict", 0);
        $param->put("weight", 1);
        $param->put("totalAmount", 0);
        $param->put("Istype", 2);
        $param->put("language", 0);

        $url = "https://api.myems.vn/EmsDosmetic?";

        $response = collect(Http::get($url, $param->toArray())->json());
        
        if ($response->get("Code") == "00") {
            $return = collect(collect($response->get("Message"))->firstWhere('Type', '1'))->mapWithKeys(function ($item, $key) {
                if ($key == "Rates") {
                    return ["amount" => Currency::convert($item, "VND", "USD")];
                }
                if ($key == "Type") {
                    return [];
                }
                return [strtolower($key) => $item];
            });

            if ($return->has("description"))
            {
                if ($return->get("description") != "QT") {
                    $return->put("description", "TC");
                }
            }
            
            return response()->json($return);
        }

        return response()->json([]);
    }
}
