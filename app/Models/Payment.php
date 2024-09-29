<?php

namespace App\Models;

use App\Casts\EncryptedByColumn;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    
    protected $fillable = [
        'order_id',
        'method',
        'info'
    ];

    protected function casts(): array
    {
        return [
            'info' => EncryptedByColumn::class. ':order_id',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
