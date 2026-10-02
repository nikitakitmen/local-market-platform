<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['Казань', 'Республика Татарстан'],
            ['Нижний Новгород', 'Нижегородская область'],
            ['Екатеринбург', 'Свердловская область'],
            ['Самара', 'Самарская область'],
        ];

        foreach ($cities as $index => [$name, $region]) {
            City::create(['name' => $name, 'region' => $region, 'sort_order' => $index]);
        }
    }
}
