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
        'shipping_address_id',
        'configuration_id',
        'payment_method',
        'status',
        'quantity',
        'amount',
        'shipping_fee',
        'is_paid'
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'status' => OrderStatus::class
        ];
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
}
