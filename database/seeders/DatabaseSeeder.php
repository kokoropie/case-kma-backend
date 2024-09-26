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
        $this->call([
            ConfigSeeder::class,
            PhoneModelSeeder::class,
            CaseColorSeeder::class,
            UserSeeder::class,
        ]);
    }
}
