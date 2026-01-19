<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $uae = Country::where('name', 'United Arab Emirates')->first();
        $morocco = Country::where('name', 'Morocco')->first();

        if (!$uae || !$morocco) {
            return;
        }

        City::insert([
            [
                'country_id' => $uae->id,
                'name' => 'Dubai',
                'image' => 'cities/dubai.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $uae->id,
                'name' => 'Abu Dhabi',
                'image' => 'cities/abu_dhabi.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $morocco->id,
                'name' => 'Marrakech',
                'image' => 'cities/marrakech.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $morocco->id,
                'name' => 'Casablanca',
                'image' => 'cities/casablanca.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
