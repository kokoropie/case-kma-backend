<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LockUser extends Model
{
    use HasFactory;

    protected $table = 'lock_users';
    protected $primaryKey = 'lock_user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reason',
        'end_at'
    ];

    protected $touches = ['user'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'end_at' => 'datetime'
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "user_id");
    }
    
}
