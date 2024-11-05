<?php
namespace App\ThirdParty\Paypal;
use Carbon\Carbon;
use Http;
use Str;

class Paypal
{
    protected float $amount = 0;
    protected string $info = '';
    private static ?self $instance = null;

    private $token = null;
    private ?Carbon $lastTokenTime = null;

    private static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self;
        }
        return static::$instance;
    }

    private function generateAccessToken()
    {
        $instance = self::getInstance();
        if (!is_null($instance->lastTokenTime) && $instance->lastTokenTime->lessThan(Carbon::now())) {
            $instance->lastTokenTime = null;
            $instance->token = null;
        }
        if (is_null($instance->token)) {
            $response = Http::withBasicAuth(config('paypal.client_id'), config('paypal.client_secret'))
                ->asForm()
                ->post(config('paypal.base_url') . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);
            $instance->token = $response->json();
            $instance->lastTokenTime = Carbon::now()->addSeconds($instance->token['expires_in']);
        }

        return $instance;
    }

    private function getAccessToken()
    {
        return self::getInstance()->generateAccessToken()->token['access_token'];
    }

    private function getTokenType()
    {
        return self::getInstance()->generateAccessToken()->token['token_type'];
    }

    public static function amount($amount = 0)
    {
        self::getInstance()->amount = $amount;
        return self::getInstance();
    }

    public static function info($info = '')
    {
        self::getInstance()->info = $info;
        return self::getInstance();
    }

    public static function create($data)
    {
        $instance = self::getInstance();

        if (!empty($data['amount'])) {
            $instance->amount = $data['amount'];
        }
        if (!empty($data['info'])) {
            $instance->info = $data['info'];
        }

        $txnRef = $data['id'] ?? Str::uuid();

        $input = [
            'intent' => Constants::INTENT_CAPTURE,
            'purchase_units' => [
                [
                    'reference_id' => $txnRef,
                    'amount' => [
                        'value' => $instance->amount,
                        'currency_code' => config('paypal.currency'),
                    ],
                    'description' => $instance->info ?? $txnRef,
                    'invoice_id' => $txnRef,
                ]
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        "brand_name" => config('app.name'),
                        "shipping_preference" => Constants::SHIPPING_PREFERENCE_NO_SHIPPING,
                        "user_action" => Constants::USER_ACTION_PAY_NOW,
                        "locale" => config('paypal.locale'),
                        "return_url" => config('paypal.return_url'),
                        "cancel_url" => config('paypal.cancel_url'),
                    ]
                ]
            ]
        ];

        $response = Http::withBasicAuth(config('paypal.client_id'), config('paypal.client_secret'))
            ->post(config('paypal.base_url') . '/v2/checkout/orders', $input);

        $link = collect($response->json()['links'])->firstWhere('rel', 'payer-action');

        return ["paypal", $response->json()['id'], $link['href']];
    }

    public static function link($param)
    {
        if (empty($param['token'])) {
            return '';
        }

        if (self::success($param)) {
            return '';
        }

        $checkout = self::checkout($param['token']);
        if (empty($checkout)) {
            return '';
        }

        $link = collect($checkout['links'])->firstWhere('rel', 'payer-action');

        return $link && isset($link['href']) ? $link['href'] : '';
    }

    private static function checkout(string $orderId)
    {
        if (empty($orderId)) {
            return null;
        }
        $response = cache()->driver('file')->remember("paypal_{$orderId}", 10, fn() => Http::withBasicAuth(config('paypal.client_id'), config('paypal.client_secret'))
            ->contentType('application/json')
            ->get(config('paypal.base_url') . '/v2/checkout/orders/' . $orderId)->json());

        return $response;
    }

    public static function success(array $param): bool
    {
        if (empty($param['token'])) {
            return false;
        }
        $token = $param['token'];
        $checkout = self::checkout($token);
        if (!$checkout) {
            return false;
        }
        return isset($checkout['status']) && $checkout['status'] === Constants::STATUS_APPROVED;
    }

    public static function details(array $param): array|null
    {
        if (empty($param['token'])) {
            return null;
        }
        $token = $param['token'];
        $checkout = self::checkout($token);
        if (!$checkout) {
            return null;
        }
        return [
            'id' => $checkout['purchase_units'][0]['invoice_id'],
            'info' => $checkout['id'],
        ];
    }
}
