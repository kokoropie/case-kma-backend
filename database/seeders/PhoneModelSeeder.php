<?php

namespace Database\Seeders;

use App\Models\PhoneModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhoneModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
    }
}
