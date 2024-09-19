<?php

namespace App\Models;

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
        'configuration_id',
        'color',
        'material',
        'model',
        'price',
    ];

    public function color()
    {
        return $this->belongsTo(CaseColor::class, 'color', 'slug');
    }

    public function material()
    {
        return $this->belongsTo(CaseMaterial::class, 'material', 'slug');
    }

    public function model()
    {
        return $this->belongsTo(PhoneModel::class, 'model', 'slug');
    }
}
