<?php

namespace App\Http\Controllers;

use App\ThirdParty\Currency\Currency;
use Http;
use Illuminate\Http\Request;
use Str;
use Validator;

class ShippingInfoController extends Controller
{
    public function country()
    {
        $url = "https://admin.ems.com.vn/api/nationalCategory?pageSize=1000";
        $data = cache()->driver('file')->remember('country', 3600, function () use ($url) {
            return Http::get($url)->json();
        });
        if ($data['code'] == 200) {
            $return = collect($data['response']['data'])
                ->map(function ($item) {
                    return [
                        "code" => $item["countryCode"],
                        "name" => Str::ascii($item["countryName"]),
                        "real_name" => $item["countryName"],
                    ];
                })
                ->sortBy('name')
                ->values();
            return response()->json(
                $return
                    ->map(function ($item) {
                        return [
                            "code" => $item["code"],
                            "name" => $item["real_name"],
                        ];
                    })
                    ->values()
            );
        }
        return response()->json([]);
    }

    public function province()
    {
        $data = collect(json_decode(
                '[{"code":"10","name":"Hà Nội"},{"code":"16","name":"Hưng Yên"},{"code":"17","name":"Hải Dương"},{"code":"18","name":"Hải Phòng"},{"code":"20","name":"Quảng Ninh"},{"code":"22","name":"Bắc Ninh"},{"code":"23","name":"Bắc Giang"},{"code":"24","name":"Lạng Sơn"},{"code":"25","name":"Thái Nguyên"},{"code":"26","name":"Bắc Kạn"},{"code":"27","name":"Cao Bằng"},{"code":"28","name":"Vĩnh Phúc"},{"code":"29","name":"Phú Thọ"},{"code":"30","name":"Tuyên Quang"},{"code":"31","name":"Hà Giang"},{"code":"32","name":"Yên Bái"},{"code":"33","name":"Lào Cai"},{"code":"35","name":"Hoà Bình"},{"code":"36","name":"Sơn La"},{"code":"38","name":"Điện Biên"},{"code":"39","name":"Lai Châu"},{"code":"40","name":"Hà Nam"},{"code":"41","name":"Thái Bình"},{"code":"42","name":"Nam Định"},{"code":"43","name":"Ninh Bình"},{"code":"44","name":"Thanh Hoá"},{"code":"46","name":"Nghệ An"},{"code":"48","name":"Hà Tĩnh"},{"code":"51","name":"Quảng Bình"},{"code":"52","name":"Quảng Trị"},{"code":"53","name":"Thừa Thiên Huế"},{"code":"55","name":"Đà Nẵng"},{"code":"56","name":"Quảng Nam"},{"code":"57","name":"Quảng Ngãi"},{"code":"58","name":"Kon Tum"},{"code":"59","name":"Bình Định"},{"code":"60","name":"Gia Lai"},{"code":"62","name":"Phú Yên"},{"code":"63","name":"Đắk Lăk"},{"code":"64","name":"Đắk Nông"},{"code":"65","name":"Khánh Hoà"},{"code":"66","name":"Ninh Thuận"},{"code":"67","name":"Lâm Đồng"},{"code":"70","name":"Hồ Chí Minh"},{"code":"79","name":"Bà Rịa Vũng Tàu"},{"code":"80","name":"Bình Thuận"},{"code":"81","name":"Đồng Nai"},{"code":"82","name":"Bình Dương"},{"code":"83","name":"Bình Phước"},{"code":"84","name":"Tây Ninh"},{"code":"85","name":"Long An"},{"code":"86","name":"Tiền Giang"},{"code":"87","name":"Đồng Tháp"},{"code":"88","name":"An Giang"},{"code":"89","name":"Vĩnh Long"},{"code":"90","name":"Cần Thơ"},{"code":"91","name":"Hậu Giang"},{"code":"92","name":"Kiên Giang"},{"code":"93","name":"Bến Tre"},{"code":"94","name":"Trà Vinh"},{"code":"95","name":"Sóc Trăng"},{"code":"96","name":"Bạc Liêu"},{"code":"97","name":"Cà Mau"}]', 
                true,
            ))
            ->map(function ($item) {
                return [
                    "code" => $item["code"],
                    "name" => Str::ascii($item["name"]),
                    "real_name" => $item["name"],
                ];
            })
            ->sortBy('name')
            ->values();

        [$filteredData, $newData] = $data->partition(function ($item) {
            return str("Ha Noi|Ho Chi Minh")->contains($item['name'], true);
        });

        while ($filteredData->count() > 0) {
            $newData->prepend($filteredData->pop());
        }

        return response()->json(
            $newData->map(function ($item) {
                return [
                    "code" => $item["code"],
                    "name" => $item["real_name"],
                ];
            })->values()
        );
    }

    public function district(string $provinceCode)
    {
        $url = "https://admin.ems.com.vn/api/districtCategory/getdistrictbyprovice?provinceCode={$provinceCode}&pageSize=1000";
        $data = cache()->driver('file')->remember("district-{$provinceCode}", 3600, function () use ($url) {
            return Http::get($url)->json();
        });

        if ($data['code'] == 200) {
            $return = collect($data['response']['data'])
                ->map(function ($item) {
                    return [
                        "code" => $item["districtcode"],
                        "name" => Str::ascii($item["districtname"]),
                        "real_name" => $item["districtname"],
                    ];
                })
                ->sortBy('name')
                ->values();
            return response()->json(
                $return
                    ->map(function ($item) {
                        return [
                            "code" => $item["code"],
                            "name" => $item["real_name"],
                        ];
                    })
                    ->values()
            );
        }

        return response()->json([]);
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
