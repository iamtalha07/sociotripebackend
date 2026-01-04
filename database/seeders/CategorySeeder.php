<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Cycling', 'icon' => 'cycling.svg'],
            ['name' => 'Golfing', 'icon' => 'golfing.svg'],
            ['name' => 'Water Sports', 'icon' => 'water_sports.svg'],
            ['name' => 'Boat Ride', 'icon' => 'boat_ride.svg'],
            ['name' => 'City Tour', 'icon' => 'city_tour.svg'],
            ['name' => 'Culture', 'icon' => 'culture.svg'],
            ['name' => 'Hiking', 'icon' => 'hiking.svg'],
            ['name' => 'Adventure', 'icon' => 'adventure.svg'],
            ['name' => 'Sky Diving', 'icon' => 'sky_diving.svg'],
            ['name' => 'Scuba Diving', 'icon' => 'scuba_diving.svg'],
            ['name' => 'Museum Visit', 'icon' => 'museum_visit.svg'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
