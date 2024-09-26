<?php

namespace Database\Seeders;

use App\Models\CaseColor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaseColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
