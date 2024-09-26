<?php 
namespace App\ThirdParty\Currency;
use Http;

class Currency
{
    private static ?self $instance = null;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self;
        }
        return static::$instance;
    }

    public static function convert($amount, ?string $from = null, string|array|null $to = null): array|float
    {
        $from = strtolower($from ?? 'usd');
        $response = cache()
            ->driver('file')
            ->remember(
                "currency_{$from}", 
                3600, 
                fn () =>
                    Http::get(
                        str(Constants::BASE_URL)->replace('{currency}', $from)
                    )->json()
            );

        $return = [];

        if (is_string($to)) {
            $to = strtolower($to);
            return round((float) ($response[$from][$to] * $amount), 2);
        } else if (is_array($to)) {
            foreach ($to as $currency) {
                $currency = strtolower($currency);
                $return[$currency] = round($response[$from][$currency] * $amount, 2);
            }
        } else {
            foreach ($response[$from] as $currency => $rate) {
                $return[$currency] = round($rate * $amount, 2);
            }
        }

        return $return;
    }
}
