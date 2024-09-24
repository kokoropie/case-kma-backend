<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CaseColor;
use App\Models\Config;
use App\Models\PhoneModel;
use App\Models\ShippingAddress;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PhoneModel::create([
            'name' => 'iPhone 12',
            'slug' => 'iphone-12',
            'image_url' => 'https://via.placeholder.com/1170x2532'
        ]);
        PhoneModel::create([
            'name' => 'iPhone 13',
            'slug' => 'iphone-13',
            'image_url' => 'https://via.placeholder.com/1170x2532'
        ]);
        PhoneModel::create([
            'name' => 'iPhone 14',
            'slug' => 'iphone-14',
            'image_url' => 'https://via.placeholder.com/1170x2532'
        ]);

        CaseColor::create([
            'name' => 'Black',
            'slug' => 'black',
            'hex_code' => '#000000'
        ]);
        CaseColor::create([
            'name' => 'White',
            'slug' => 'white',
            'hex_code' => '#ffffff'
        ]);
        CaseColor::create([
            'name' => 'Red',
            'slug' => 'red',
            'hex_code' => '#ff0000'
        ]);

        $config = new Config();
        $config->key = 'base_fee';
        $config->value = [
            'value' => 14.00,
        ];
        $config->save();

        $config = new Config();
        $config->key = 'material_fee';
        $config->value = [
            'value' => 5.00,
        ];
        $config->save();

        $config = new Config();
        $config->key = 'finish_fee';
        $config->value = [
            'value' => 3.00,
        ];
        $config->save();

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
