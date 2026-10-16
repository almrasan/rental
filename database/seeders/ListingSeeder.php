<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Facades\Hash;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'demo.owner@rentalportal.lk'],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('password123'),
                'phone' => '0771234567',
                'role' => 'user',
            ]
        );

        $districts = ['Colombo', 'Kandy', 'Galle', 'Jaffna', 'Kurunegala'];

        $sampleListings = [
            ['category' => 'Bike', 'title' => 'Honda Dio Scooter', 'price' => 1500, 'keyword' => 'motorbike'],
            ['category' => 'Bike', 'title' => 'Yamaha FZ 150cc', 'price' => 2000, 'keyword' => 'motorcycle'],
            ['category' => 'Van', 'title' => 'Toyota HiAce Van', 'price' => 8000, 'keyword' => 'van'],
            ['category' => 'Van', 'title' => 'Nissan Caravan', 'price' => 7500, 'keyword' => 'minivan'],
            ['category' => 'Bus', 'title' => '25-Seater Mini Bus', 'price' => 15000, 'keyword' => 'bus'],
            ['category' => 'Car', 'title' => 'Toyota Axio 2018', 'price' => 6000, 'keyword' => 'sedan,car'],
            ['category' => 'Car', 'title' => 'Suzuki Alto', 'price' => 4000, 'keyword' => 'hatchback,car'],
            ['category' => 'House', 'title' => '3 Bedroom House - Fully Furnished', 'price' => 45000, 'keyword' => 'house,home'],
            ['category' => 'House', 'title' => 'Cozy 2BR Apartment', 'price' => 30000, 'keyword' => 'apartment,interior'],
        ];

        foreach ($sampleListings as $index => $item) {
            $category = Category::where('name', $item['category'])->first();

            if (!$category) {
                continue;
            }

            $listing = Listing::create([
                'user_id' => $owner->id,
                'category_id' => $category->id,
                'title' => $item['title'],
                'description' => 'Well maintained ' . strtolower($item['category']) . ' available for rent. Clean, reliable, and ready to use. Contact owner for more details and availability.',
                'price' => $item['price'],
                'district' => $districts[$index % count($districts)],
                'status' => 'approved',
                'is_available' => true,
            ]);

            for ($i = 1; $i <= 2; $i++) {
                $listing->images()->create([
                    'image_path' => "https://loremflickr.com/600/400/{$item['keyword']}?lock=" . ($index * 10 + $i),
                ]);
            }
        }
    }
}
