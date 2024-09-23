<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CaseColor;
use App\Models\PhoneModel;
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
    }
}
