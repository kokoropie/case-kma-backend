<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'key' => 'base_fee',
                'value' => [
                    'value' => 14.00,
                ],
            ],
            [
                'key' => 'material_fee',
                'value' => [
                    'value' => 5.00,
                ],
            ],
            [
                'key' => 'finish_fee',
                'value' => [
                    'value' => 3.00,
                ],
            ],
            [
                'key' => 'from_province',
                'value' => [
                    'value' => '70',
                ],
            ],
            [
                'key' => 'from_district',
                'value' => [
                    'value' => '7360'
                ]
            ]
        ];

        foreach ($configs as $config) {
            Config::firstOrCreate(collect($config)->only('key')->toArray(), $config);
        }
    }
}
