<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'configurations';
    protected $primaryKey = 'configuration_id';
    public $timestamps = false;

    protected $fillable = [
        'color',
        'material',
        'model',
        'finish',
        'height',
        'width',
        'image_url',
        'cropped_image_url',
        'amount',
        'amount_material',
        'amount_finish',
    ];

    protected $appends = ['total_amount'];

    public function color()
    {
        return $this->belongsTo(CaseColor::class, 'color', 'slug');
    }

    public function model()
    {
        return $this->belongsTo(PhoneModel::class, 'model', 'slug');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'configuration_id', 'configuration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                return $this->amount + $this->amount_material + $this->amount_finish;
            }
        );
    }

    protected static function booted(): void
    {
        static::created(function() {
            cache()->tags('dashboard', 'configurations')->flush();
        });
    }
}
