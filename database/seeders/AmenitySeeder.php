<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Flexible Schedule', 'icon' => 'flexible_schedule.svg'],
            ['name' => 'Tour Guide', 'icon' => 'tour_guide.svg'],
            ['name' => 'Meals Included', 'icon' => 'meals_included.svg'],
            ['name' => 'Free Internet', 'icon' => 'free_internet.svg'],
            ['name' => 'Child-Friendly Activities', 'icon' => 'child_friendly.svg'],
            ['name' => 'Hotel Pickup', 'icon' => 'hotel_pickup.svg'],
            ['name' => 'No Extra Fees', 'icon' => 'no_extra_fees.svg'],
            ['name' => 'VIP Access', 'icon' => 'vip_access.svg'],
            ['name' => 'Mobile Tickets', 'icon' => 'mobile_tickets.svg'],
            ['name' => 'Beach Gear Included', 'icon' => 'beach_gear.svg'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
