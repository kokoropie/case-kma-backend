<?php

namespace App\Models;

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
        'street',
        'province',
        'city',
        'postal_code',
        'country'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id', 'shipping_address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
