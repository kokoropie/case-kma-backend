<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseColor extends Model
{
    use HasFactory;

    protected $table = 'case_colors';
    protected $primaryKey = 'slug';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
        'hex_code',
    ];

    public function configurations()
    {
        return $this->hasMany(Configuration::class, 'color', 'slug');
    }
}
