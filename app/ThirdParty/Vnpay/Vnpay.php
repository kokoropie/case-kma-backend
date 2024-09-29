<?php 
namespace App\ThirdParty\Vnpay;
use Carbon\Carbon;
use Str;

class Vnpay
{
    protected float $amount = 0;
    protected string $info = '';
    protected string $bank_code = '';
    private static ?self $instance = null;

    private static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self;
        }
        return static::$instance;
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

    public static function bankCode($bank_code = '')
    {
        self::getInstance()->bank_code = $bank_code;
        return self::getInstance();
    }

    public static function payqr()
    {
        return self::bankCode(Constants::BANK_CODE_VNPAYQR);
    }

    public static function bank()
    {
        return self::bankCode(Constants::BANK_CODE_VNBANK);
    }

    public static function card()
    {
        return self::bankCode(Constants::BANK_CODE_INTCARD);
    }

    public static function create($data)
    {
        $instance = self::getInstance();

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $txnRef = $data['id'] ?? Str::uuid();

        if (!empty($data['amount'])) {
            $instance->amount = $data['amount'];
        }
        if (!empty($data['info'])) {
            $instance->info = $data['info'];
        }
        if (!empty($data['bank_code'])) {
            $instance->bank_code = $data['bank_code'];
        }

        $input = [
            'Version' => config('vnpay.version'),
            'TmnCode' => config('vnpay.tmn_code'),
            'Amount' => $instance->amount * 100,
            'Command' => Constants::COMMAND_PAY,
            'CreateDate' => date('YmdHis'),
            'CurrCode' => Constants::CURRENCY,
            'IpAddr' => request()->ip(),
            'Locale' => Constants::LOCALE,
            'OrderInfo' => str($instance->info)->ascii()->toString() ?: $txnRef,
            'OrderType' => Constants::ORDER_TYPE,
            'ReturnUrl' => config('vnpay.return_url'),
            'TxnRef' => $txnRef . "|" . time(),
            'ExpireDate' => Carbon::now()->addMinutes(config('vnpay.expire'))->format('YmdHis'),
        ];

        if (!empty($instance->bank_code)) {
            $input['BankCode'] = $instance->bank_code;
        }

        ksort($input);

        $data = collect($input)->map(function ($value, $key) {
            return urlencode(Constants::PREFIX . $key) . '=' . urlencode($value);
        })->implode('&');

        $url = config('vnpay.base_url') . Constants::PATH_PAYMENT . '?' . $data;

        $hash_secret = config('vnpay.hash_secret');

        if (!empty($hash_secret)) {
            $url .= '&' . Constants::PREFIX . 'SecureHash=' . hash_hmac('sha512', $data, $hash_secret);
        }

        date_default_timezone_set(config('app.timezone'));

        return ["vnpay", "", $url];
    }

    public static function link($param) 
    {
        return static::create($param);
    }

    private static function verify(array $param): bool
    {
        $checkKeys = collect([
            "Amount",
            "BankCode",
            "BankTranNo",
            "CardType",
            "OrderInfo",
            "PayDate",
            "ResponseCode",
            "TmnCode",
            "TransactionNo",
            "TransactionStatus",
            "TxnRef",
            "SecureHash"
        ])->sort()->map(function ($value) {
            return Constants::PREFIX . $value;
        });

        $hash_secret = config('vnpay.hash_secret');

        $data = collect($param)->filter(function ($value, $key) use ($checkKeys) {
            return $checkKeys->contains($key);
        })->sortKeys();

        $secureHash = $data->pull(Constants::PREFIX . 'SecureHash');

        $data = $data->map(function ($value, $key) {
            return $key . '=' . urlencode($value);
        })->implode('&');

        return $secureHash === hash_hmac('sha512', $data, $hash_secret);
    }

    public static function success(array $param): bool
    {
        if (!self::verify($param)) {
            return false;
        }
        return $param[Constants::PREFIX . 'ResponseCode'] == Constants::RESPONSE_CODE_SUCCESS;
    }

    public static function details(array $param): array|null
    {
        if (!self::verify($param)) {
            return null;
        }
        return [
            'id' => str($param[Constants::PREFIX . 'TxnRef'])->explode('|')->first(),
            'info' => $param[Constants::PREFIX . 'TransactionNo']
        ];
    }
}
