<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CultureKingsProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                "name" => "Saint Morta Serenity Oversized Muscle T-Shirt White",
                "description" => "A premium oversized muscle tee featuring a minimalist aesthetic, perfect for layering or standalone wear. Made from high-quality cotton.",
                "price" => 39.00,
                "category" => "t_shirt",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/02055560-YW100_mens_0010.jpg?v=1763010615&width=800"]
            ],
            [
                "name" => "Loiter X WWE Stone Cold Beef Vintage Zip Hoodie Black",
                "description" => "Exclusive collaboration piece featuring vintage Stone Cold Steve Austin graphics. Heavyweight zip-up hoodie with a relaxed fit.",
                "price" => 95.00,
                "category" => "hoodie",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/02056220-YB001_mens_0010.jpg?v=1775692660&width=800"]
            ],
            [
                "name" => "New Balance 9060 Blue Rain/Rain Cloud",
                "description" => "The 9060 is a new expression of the refined style and innovation-led design that has made the 99X series home to some of the most iconic models in New Balance history.",
                "price" => 150.00,
                "category" => "sneakers",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/05013528-YB026_default_0010.jpg?v=1758755423&width=800"]
            ],
            [
                "name" => "MNML Super Stacked Denim Jeans White",
                "description" => "Signature stacked fit denim with a tapered leg and extra length for the perfect 'stack' over your sneakers. Durable white denim.",
                "price" => 80.00,
                "category" => "pants",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/03014011-YR002_mens_0010.jpg?v=1775692672&width=800"]
            ],
            [
                "name" => "Pro Compression Classic NBA Logoman Crew Sock Green",
                "description" => "Official NBA Logoman crew socks featuring compression technology for comfort and performance. Ribbed cuffs and cushioning throughout.",
                "price" => 14.00,
                "category" => "accessories",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/05013815-YB004_default_0010.jpg?v=1775782217&width=800"]
            ],
            [
                "name" => "Nike Sportswear Club Fleece Hoodie Grey",
                "description" => "A closet staple, the Nike Sportswear Club Fleece Pullover Hoodie combines classic style with the soft comfort of fleece.",
                "price" => 65.00,
                "category" => "hoodie",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/02056156-YB001_mens_0010.jpg?v=1775564233&width=800"]
            ],
            [
                "name" => "Adidas Gazelle Indoor Yellow/Black",
                "description" => "Classic indoor soccer silhouette with a vibrant yellow suede upper and black stripes. Timeless street style.",
                "price" => 120.00,
                "category" => "sneakers",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/05013620-YW001_default_0010.jpg?v=1768461234&width=800"]
            ],
            [
                "name" => "MNML Ultra Wide Tribal Denim Jeans Medium Blue",
                "description" => "Ultra-wide leg denim featuring custom tribal embroidery. Distressed finish for a vintage streetwear look.",
                "price" => 76.00,
                "category" => "pants",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/03014120-YB001_mens_0010.jpg?v=1774928927&width=800"]
            ],
            [
                "name" => "Saint Morta Punk Tour T-Shirt Washed Charcoal",
                "description" => "Heavyweight cotton tee with a washed finish and tour-inspired graphic prints on the front and back.",
                "price" => 36.00,
                "category" => "t_shirt",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/02055568-YC071_mens_0010.jpg?v=1763008327&width=800"]
            ],
            [
                "name" => "Asics Gel-Kayano 14 Black/Silver",
                "description" => "The GEL-KAYANO 14 running shoe resurfaces with its late 2000s aesthetic as a nod to our storied GEL-KAYANO series.",
                "price" => 160.00,
                "category" => "sneakers",
                "image_urls" => ["https://cdn.shopify.com/s/files/1/2114/6461/files/05013421-YB001_default_0010.jpg?v=1762811866&width=800"]
            ]
        ];

        $stores = Store::all();
        if ($stores->isEmpty()) {
            $this->command->error('No stores found. Please run DatabaseSeeder first.');
            return;
        }

        $totalToCreate = 140;
        $createdCount = 0;

        $this->command->info("Starting to seed $totalToCreate products...");

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/products'))) {
            mkdir(storage_path('app/public/products'), 0755, true);
        }

        while ($createdCount < $totalToCreate) {
            foreach ($templates as $template) {
                if ($createdCount >= $totalToCreate) break;

                $store = $stores->random();
                
                // Randomize name slightly for uniqueness if needed, but the user said "random products"
                // Adding a random color or variation
                $colors = ['Black', 'White', 'Navy', 'Grey', 'Red', 'Sand', 'Olive'];
                $color = $colors[array_rand($colors)];
                $name = $template['name'] . " - " . $color;

                $product = Product::create([
                    'store_id' => $store->id,
                    'name' => $name,
                    'description' => $template['description'],
                    'price' => $template['price'] + rand(-10, 20),
                    'stock' => rand(5, 50),
                    'category' => $template['category'],
                    'status' => 'active',
                ]);

                foreach ($template['image_urls'] as $idx => $url) {
                    try {
                        $imageContent = @file_get_contents($url);
                        if ($imageContent) {
                            $filename = 'prod_' . Str::slug($name) . '_' . time() . '_' . $idx . '.jpg';
                            $path = 'products/' . $filename;
                            Storage::disk('public')->put($path, $imageContent);

                            ProductImage::create([
                                'product_id' => $product->id,
                                'image_path' => $path,
                                'sort_order' => $idx,
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->command->warn("Failed to download image for product: " . $name);
                    }
                }

                $createdCount++;
                if ($createdCount % 10 == 0) {
                    $this->command->info("Created $createdCount products...");
                }
            }
        }

        $this->command->info("Successfully seeded $createdCount products.");
    }
}
