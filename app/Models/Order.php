<?php

namespace App\Models;

use App\Enum\OrderStatus;
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
        'billing_address_id',
        'shipping_address_id',
        'configuration_id',
        'status',
        'amount',
        'is_paid'
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'status' => OrderStatus::class
        ];
    }

    public function billingAddress()
    {
        return $this->belongsTo(BillingAddress::class, 'billing_address_id', 'billing_address_id');
    }

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

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price * $this->configuration->price,
        );
    }
}
