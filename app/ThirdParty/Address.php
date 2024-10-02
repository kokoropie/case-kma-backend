<?php 
namespace App\ThirdParty;
use App\ThirdParty\Currency\Currency;
use Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Str;

class Address
{
    public static function country(string|array|null $code = null): array|null
    {
        if (empty($code)) $code = null;
        if (is_array($code) && count($code) == 1) $code = (string) $code[0];
        
        $urlPhone = "https://gist.githubusercontent.com/kcak11/4a2f22fb8422342b3b3daa7a1965f4e4/raw/6a23d2217b0476a326958f97cb1da1865af83a1f/countries.json";
        $url = "https://admin.ems.com.vn/api/nationalCategory?pageSize=1000";
        $dataPhone = collect(cache()->driver('file')->remember('countryPhone', 3600, function () use ($urlPhone) {
            return Http::get($urlPhone)->json();
        }));
        $data = collect(cache()->driver('file')->remember('country', 3600, function () use ($url) {
            return Http::get($url)->json();
        }));
        if ($data->get("code") == 200) {
            $return = collect($data->get("response")['data'])
                ->map(function ($item) use ($dataPhone) {
                    $data = $dataPhone->firstWhere('isoCode', strtoupper($item["countryCode"]));
                    return [
                        "code" => strtoupper($item["countryCode"]),
                        "name" => Str::ascii($item["countryName"]),
                        "real_name" => $item["countryName"],
                        "dial" => $data['dialCode'] ?? null,
                        "flag" => $data["flag"] ?? null
                    ];
                })
                ->sortBy('name')
                ->values();
            $return->transform(function ($item) {
                return [
                    "code" => $item["code"],
                    "name" => $item["real_name"],
                    "dial" => $item["dial"],
                    "flag" => $item["flag"],
                ];
            });
            $return->prepend([
                "code" => "VN",
                "name" => $dataPhone->firstWhere('isoCode', "VN")["name"],
                "dial" => $dataPhone->firstWhere('isoCode', "VN")["dialCode"],
                "flag" => $dataPhone->firstWhere('isoCode', "VN")["flag"],
            ]);
            if (is_string($code)) {
                return $return->firstWhere('code', strtoupper($code));
            } else if (is_array($code)) {
                return $return->whereIn('code', array_map('strtoupper', $code))->values()->toArray();
            }
            return $return->values()->toArray();
        }
        return [];
    }

    public static function countryExists(string $code): bool
    {
        return collect(self::country($code))->isNotEmpty();
    }
    
    public static function countryDoesntExist(string $code): bool
    {
        return !self::countryExists($code);
    }

    public static function province(string|array|null $code = null): array|null
    {
        if (empty($code)) $code = null;
        if (is_array($code) && count($code) == 1) $code = (string) $code[0];

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

        $newData->transform(function ($item) {
            return [
                "code" => $item["code"],
                "name" => $item["real_name"],
            ];
        });
        if (is_string($code)) {
            return $newData->firstWhere('code', strtoupper($code));
        } else if (is_array($code)) {
            return $newData->whereIn('code', array_map('strtoupper', $code))->values()->toArray();
        }
        return $newData->values()->toArray();
    }

    public static function provinceExists(string $code): bool
    {
        return collect(self::province($code))->isNotEmpty();
    }

    public static function provinceDoesntExist(string $code): bool
    {
        return !self::provinceExists($code);
    }

    public static function district(string|array $provinceCode, string|array|null $code = null): array|null
    {
        if (empty($code)) $code = null;
        if (is_array($provinceCode) && count($provinceCode) == 1) $provinceCode = (string) $provinceCode[0];
        if (is_array($code) && count($code) == 1) {
            $code = (string) $code[0];
        }
        if (is_array($code) && is_array($provinceCode)) {
            throw new \Exception("If code is array, province must be string", 1);
        }
        if (is_string($provinceCode)) {
            $provinceCode = [$provinceCode];
        }

        $responses = cache()->driver('file')->remember("district-" . implode('-', $provinceCode), 3600, function () use ($provinceCode) {
            $responses = Http::pool(fn (Pool $pool) => collect($provinceCode)->map(function ($code) use ($pool) {
                $url = "https://admin.ems.com.vn/api/districtCategory/getdistrictbyprovice?provinceCode={$code}&pageSize=1000";
                $pool->as("{$code}")->get($url);
            })->toArray());
            foreach ($responses as $key => $response) {
                $responses["{$key}"] = $response->json();
            }
            return $responses;
        });

        $data = collect($responses)
            ->map(function ($response, $key) {
                if ($response["code"] != 200) {
                    return [
                        "province" => $key,
                        "data" => []
                    ];
                }
                $data = collect($response['response']['data'])
                    ->map(function ($item) {
                        return [
                            "code" => $item["districtcode"],
                            "name" => Str::ascii($item["districtname"]),
                            "real_name" => $item["districtname"],
                        ];
                    })
                    ->sortBy('name')
                    ->values();
                $data->transform(function ($item) {
                    return [
                        "code" => $item["code"],
                        "name" => $item["real_name"],
                    ];
                });
                return [
                    "province" => $key,
                    "data" => $data->values()->toArray()
                ];
            });

        if (count($provinceCode) == 1) {
            $data = collect($data->first()['data'])->values();
        }

        if (is_string($code)) {
            $data = collect($data->firstWhere('code', $code));
        } else if (is_array($code)) {
            $data = $data->whereIn('code', $code)->values();
        }

        $return = $data->toArray();

        return $return;
    }

    public static function districtExists(string $provinceCode, string $code): bool
    {
        return collect(self::district($provinceCode, $code))->isNotEmpty();
    }

    public static function districtDoesntExist(string $provinceCode, string $code): bool
    {
        return !self::districtExists($provinceCode, $code);
    }

    public static function cost(array|Collection $param): array
    {
        $param = collect($param);

        $param->put("weight", 1);
        $param->put("totalAmount", 0);
        $param->put("Istype", 2);
        $param->put("language", 0);

        if (!str($param->get("CountryCode"))->is("VN")) {
            $param->put("ToProvince", "0");
            $param->put("ToDistrict", "0");
        }

        $url = "https://api.myems.vn/EmsDosmetic?";

        $response = collect(Http::get($url, $param->toArray())->json());
        
        if (str($response->get("Code"))->is("00")) {
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
            
            return $return->toArray();
        }
        return [];
    }
}