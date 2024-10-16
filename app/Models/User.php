<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['is_admin', 'is_lock'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->role === 'admin',
            set: fn ($value) => $this->role = $value ? 'admin' : 'user'
        );
    }

    public function isLock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->lock()->exists()
        );
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class, 'user_id', 'user_id');
    }

    public function configurations()
    {
        return $this->hasMany(Configuration::class, 'user_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function lock()
    {
        return $this->hasOne(LockUser::class, "user_id", "user_id");
    }
    
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    protected static function booted(): void
    {
        static::created(function() {
            cache()->tags('dashboard', 'users')->flush();
            cache()->tags('users')->flush();
        });

        static::updated(function() {
            cache()->tags('dashboard', 'users')->flush();
            cache()->tags('users')->flush();
        });
    }
}
