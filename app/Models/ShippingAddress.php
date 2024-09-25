<?php

namespace App\Models;

use App\ThirdParty\Address;
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
        'district',
        'province',
        'postal_code',
        'country'
    ];

    protected $appends = ['full_address'];

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
                $country = Address::country($this->country);
                if ($country["code"] === 'VN') {
                    $province = Address::province($this->province);
                    $district = Address::district($this->province, $this->district);
                    return "{$this->address}, {$district["name"]}, {$province["name"]}, {$country["name"]}";
                }
                return "{$this->address}, {$this->district}, {$this->province}, {$this->postal_code}, {$country["name"]}";
            }
        );
    }
}
