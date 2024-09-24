<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shipping_addresses';
    protected $primaryKey = 'shipping_address_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'province',
        'postal_code',
        'country',
        'fee'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id', 'shipping_address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                return "{$this->address}, {$this->province}, {$this->postal_code}, {$this->country}";
            }
        );
    }
}
