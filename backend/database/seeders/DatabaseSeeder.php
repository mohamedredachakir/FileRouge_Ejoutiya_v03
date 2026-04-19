<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Account
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $brands = [
            ['name' => 'VINTAGE SUPPLY', 'bio' => 'Premium vintage streetwear curated for the bold.'],
            ['name' => 'NEON NIGHTS', 'bio' => 'Cyberpunk aesthetics and techwear essentials.'],
            ['name' => 'DRIP MONSTER', 'bio' => 'The ultimate destination for hypebeasts and collectors.'],
            ['name' => 'STREET REBELS', 'bio' => 'Breaking the rules of fashion since 1998.'],
            ['name' => 'CYBER CORE', 'bio' => 'High-performance techwear for the digital age.'],
            ['name' => 'URBAN NOMAD', 'bio' => 'Minimalist essentials for the modern city dweller.'],
            ['name' => 'CASABLANCA CULTE', 'bio' => 'Moroccan heritage meets modern street style.'],
            ['name' => 'NOCTURNAL', 'bio' => 'Designed for those who own the night.'],
            ['name' => 'RAW DISTRICT', 'bio' => 'Unfinished aesthetics and industrial vibes.'],
            ['name' => 'SILENT THEORY', 'bio' => 'Speak through your style. Loudly.'],
            ['name' => 'LOST SOULS', 'bio' => 'Finding beauty in the forgotten corners of the streets.'],
            ['name' => 'HIDDEN ATELIER', 'bio' => 'Exclusive drops for those in the know.'],
            ['name' => 'AFTER DARK', 'bio' => 'Gothic streetwear for the mysterious soul.'],
            ['name' => 'STATIC MOTION', 'bio' => 'Energy captured in every thread.'],
            ['name' => 'MIDNIGHT CLUB', 'bio' => 'Racing culture meets urban fashion.'],
            ['name' => 'COLD CULTURE', 'bio' => 'Stay fresh, stay frozen in the heat of the city.'],
            ['name' => 'HYPER FOCUS', 'bio' => 'Precision engineering for your wardrobe.'],
            ['name' => 'OUTSIDER', 'bio' => 'For those who never fit into the box.'],
            ['name' => 'REVERSE LOGIC', 'bio' => 'Question everything. Wear the answer.'],
            ['name' => 'ABSTRACT DEPOT', 'bio' => 'Where art and streetwear collide.'],
            ['name' => 'LIMITLESS', 'bio' => 'No boundaries. No rules. Just street.'],
            ['name' => 'ESSENTIAL VOID', 'bio' => 'Perfectly empty. Perfectly style.'],
        ];

        $heroImages = [
            "https://images.pexels.com/photos/20175100/pexels-photo-20175100/free-photo-of-young-man-with-tattoos-wearing-a-casual-outfit-crouching-on-a-pavement.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5319298/pexels-photo-5319298.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/9059278/pexels-photo-9059278.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/36084095/pexels-photo-36084095/free-photo-of-urban-streetwear-fashion-in-city-setting.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5236996/pexels-photo-5236996.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5560606/pexels-photo-5560606.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5236997/pexels-photo-5236997.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/7043969/pexels-photo-7043969.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/9258235/pexels-photo-9258235.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5238215/pexels-photo-5238215.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/34967442/pexels-photo-34967442/free-photo-of-urban-style-young-adult-sitting-outdoors.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/34802398/pexels-photo-34802398/free-photo-of-street-style-fashion-in-urban-moroccan-setting.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5560194/pexels-photo-5560194.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/35203717/pexels-photo-35203717/free-photo-of-streetwear-fashion-in-sao-paulo-skatepark.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/34967440/pexels-photo-34967440/free-photo-of-urban-fashion-scene-with-vibrant-street-style.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5560294/pexels-photo-5560294.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/14481190/pexels-photo-14481190.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/32085516/pexels-photo-32085516/free-photo-of-urban-street-photography-under-bridge.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/35203735/pexels-photo-35203735/free-photo-of-urban-streetwear-fashion-scene-in-sao-paulo.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5692478/pexels-photo-5692478.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/35203716/pexels-photo-35203716/free-photo-of-streetwear-fashion-in-presidente-prudente.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/15336564/pexels-photo-15336564/free-photo-of-young-man-sitting-on-a-barrel-and-wearing-nike-dunk-high-retro-shoes.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/15336574/pexels-photo-15336574/free-photo-of-posing-man-standing-on-an-empty-city-street.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/35203726/pexels-photo-35203726/free-photo-of-street-style-fashion-in-presidente-prudente.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/12859417/pexels-photo-12859417.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/5358439/pexels-photo-5358439.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/15965791/pexels-photo-15965791/free-photo-of-woman-posing-on-street-in-city.png?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/17268853/pexels-photo-17268853/free-photo-of-man-jumping-over-street-in-town.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/34802366/pexels-photo-34802366/free-photo-of-trendy-youth-posing-in-urban-streetwear-with-skateboard.jpeg?auto=compress&cs=tinysrgb&w=1200",
            "https://images.pexels.com/photos/33090603/pexels-photo-33090603/free-photo-of-casual-urban-streetwear-style-in-sao-paulo.jpeg?auto=compress&cs=tinysrgb&w=1200"
        ];

        // Ensure stores directory exists
        $storesDir = storage_path('app/public/stores');
        if (!file_exists($storesDir)) {
            mkdir($storesDir, 0755, true);
        }

        foreach ($brands as $index => $brand) {
            $email = "owner" . ($index + 1) . "@example.com";
            $owner = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Brand Owner ' . ($index + 1),
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'store_owner',
                ]
            );

            $heroUrl = $heroImages[$index % count($heroImages)];
            $heroFileName = "hero-" . ($index + 1) . "-" . time() . ".jpg";
            $heroPath = "stores/" . $heroFileName;
            
            // Download and save hero image
            try {
                $imageContent = file_get_contents($heroUrl);
                if ($imageContent) {
                    file_put_contents(storage_path("app/public/" . $heroPath), $imageContent);
                } else {
                    $heroPath = $heroUrl; // fallback
                }
            } catch (\Exception $e) {
                $heroPath = $heroUrl; // fallback
            }

            \App\Models\Store::updateOrCreate(
                ['user_id' => $owner->id],
                [
                    'store_name' => $brand['name'],
                    'bio' => $brand['bio'],
                    'logo' => "https://images.unsplash.com/photo-".(1614850000000 + ($index * 10000))."?q=80&w=200&h=200&auto=format&fit=crop",
                    'hero_image' => $heroPath,
                    'status' => 'active',
                ]
            );
        }

        // Migrate Fake Products (Copying to storage)
        $jsonPath = base_path('../fakeproducts/_summary.json');
        if (file_exists($jsonPath)) {
            $productsData = json_decode(file_get_contents($jsonPath), true);
            $stores = \App\Models\Store::all();
            $storeCount = $stores->count();

            if ($storeCount > 0) {
                // Ensure target directory exists
                $targetDir = storage_path('app/public/products');
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                foreach ($productsData as $i => $p) {
                    $targetStore = $stores[$i % $storeCount];
                    $urlParts = explode('/', rtrim($p['source_url'], '/'));
                    $slug = end($urlParts);
                    
                    $daPrice = $p['price'] > 0 ? (int)($p['price'] * 180) : rand(4500, 12000);
                    $displayDescription = "Elevate your street game with the {$p['name']}. A premium " . str_replace('_', ' ', $p['category']) . " designed for maximum comfort and style.";

                    $product = $targetStore->products()->updateOrCreate(
                        ['name' => $p['name']],
                        [
                            'description' => $displayDescription,
                            'price' => $daPrice,
                            'stock' => rand(10, 30),
                            'category' => $p['category'],
                            'status' => 'active',
                        ]
                    );

                    // Copy Images and Add to DB
                    $product->images()->delete();
                    foreach ($p['images'] as $imgIdx => $imgRelPath) {
                        $sourceImg = base_path("../fakeproducts/{$slug}/{$imgRelPath}");
                        if (file_exists($sourceImg)) {
                            $extension = pathinfo($sourceImg, PATHINFO_EXTENSION);
                            $newFileName = Str::slug($p['name']) . '-' . $imgIdx . '-' . time() . '.' . $extension;
                            $dbPath = "products/{$newFileName}";
                            
                            copy($sourceImg, storage_path("app/public/{$dbPath}"));

                            $product->images()->create([
                                'image_path' => $dbPath,
                                'sort_order' => $imgIdx
                            ]);
                        }
                    }
                }
            }
        }

        $this->call([
            CultureKingsProductsSeeder::class,
            ClientAndOrdersSeeder::class,
        ]);
    }
}
