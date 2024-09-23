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
        'color',
        'material',
        'model',
        'finish',
        'height',
        'width',
        'image_url',
        'cropped_image_url',
    ];

    public function color()
    {
        return $this->belongsTo(CaseColor::class, 'color', 'slug');
    }

    public function model()
    {
        return $this->belongsTo(PhoneModel::class, 'model', 'slug');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
