<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneModel extends Model
{
    use HasFactory;

    protected $table = 'phone_models';
    protected $primaryKey = 'slug';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function configurations()
    {
        return $this->hasMany(Configuration::class, 'model', 'slug');
    }
}
