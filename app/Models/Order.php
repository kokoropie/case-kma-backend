<?php

namespace App\Models;

use App\Enum\OrderStatus;
use App\ThirdParty\Currency\Currency;
use App\ThirdParty\Paypal\Paypal;
use App\ThirdParty\Vnpay\Vnpay;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'shipping_address_id',
        'configuration_id',
        'payment_method',
        'status',
        'quantity',
        'amount',
        'shipping_fee',
        'is_paid'
    ];

    protected $hidden = [
        'user_id',
        'payment'
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'status' => OrderStatus::class
        ];
    }

    protected $appends = ['total_amount'];

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address_id', 'shipping_address_id');
    }

    public function configuration()
    {
        return $this->belongsTo(Configuration::class, 'configuration_id', 'configuration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    public function link(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
                if ($this->is_paid) {
                    return null;
                }
                $payment = $this->payment()->first();
                if (!$payment) {
                    $method = match($this->payment_method) {
                        'paypal', 'card' => 'paypal',
                        'vnpay', 'bank' => 'vnpay',
                        default => null,
                    };
                    $payment = new Payment();
                    $payment->order_id = $this->order_id;
                    $payment->method = $method;
                    $payment->info = "";
                    $payment->save();
                } 
                $class = match ($payment->method) {
                    'paypal' => Paypal::class,
                    'vnpay' => Vnpay::class,
                    default => null,
                };
                if ($class) {
                    if (!$payment->info) {
                        [$method, $info, $url] = match ($this->payment_method) {
                            'paypal', 'card' => Paypal::amount($this->total_amount)->create([
                                'id' => $this->order_id
                            ]),
                            'vnpay' => Vnpay::amount(Currency::convert($this->total_amount, 'USD', 'VND'))->payqr()->create([
                                'id' => $this->order_id
                            ]),
                            'bank' => Vnpay::amount(Currency::convert($this->total_amount, 'USD', 'VND'))->bank()->create([
                                'id' => $this->order_id
                            ]),
                        };
                        $payment->info = $info;

                        $payment->save();

                        return $url;
                    }
                    $data = match ($payment->method) {
                        'paypal' => [
                            'token' => $payment->info
                        ],
                        'vnpay' => [
                            'amount' => Currency::convert($this->total_amount, 'USD', 'VND'),
                            'id' => $this->order_id,
                            'bank_code' => $this->payment_method == "vnpay" ? \App\ThirdParty\Vnpay\Constants::BANK_CODE_VNPAYQR : \App\ThirdParty\Vnpay\Constants::BANK_CODE_VNBANK
                        ],
                    };
    
                    return $class::link($data);
                }
                return null;
            }
        );
    }

    public function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                return $this->amount * $this->quantity + $this->shipping_fee;
            }
        );
    }

    protected static function booted(): void
    {
        static::created(function() {
            cache()->tags('dashboard', 'orders')->flush();
            cache()->tags('orders')->flush();
            cache()->tags('users')->flush();
        });

        static::updated(function() {
            cache()->tags('dashboard', 'orders')->flush();
            cache()->tags('orders')->flush();
            cache()->tags('users')->flush();
        });
    }
}
