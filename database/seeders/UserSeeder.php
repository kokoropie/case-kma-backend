<?php

namespace Database\Seeders;

use App\Models\ShippingAddress;
use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!app()->environment('production')) {
            unlink(storage_path('logs/laravel.log'));
            $user = User::forceCreate([
                'name' => 'Test User',
                'email' => 'test@gmail.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin'
            ]);
    
            $token = str($user->createToken('api')->plainTextToken)->explode('|')[1];
            info("API Token: {$token}");
    
            ShippingAddress::forceCreate([
                'user_id' => $user->user_id,
                'name' => $user->name,
                'phone_number' => '+84123456789',
                'address' => '17A Đ. Cộng Hòa, Phường 4',
                'district' => '7360',
                'province' => '70',
                'postal_code' => '',
                'country' => 'VN'
            ]);
        }
    }
}
