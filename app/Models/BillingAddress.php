<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'billing_addresses';
    protected $primaryKey = 'billing_address_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'street',
        'province',
        'city',
        'postal_code',
        'country'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'billing_address_id', 'billing_address_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
