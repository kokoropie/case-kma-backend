<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseMaterial extends Model
{
    use HasFactory;

    protected $table = 'case_materials';
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
        return $this->hasMany(Configuration::class, 'material', 'slug');
    }
}
