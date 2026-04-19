<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientAndOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'John Doe',
                'email' => 'client@ejoutiya.com',
                'phone' => '0612345678',
                'city' => 'Casablanca',
                'address' => '123 Boulevard d\'Anfa',
                'zip_code' => '20000',
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima@ejoutiya.com',
                'phone' => '0698765432',
                'city' => 'Marrakech',
                'address' => '45 Rue de la Liberte',
                'zip_code' => '40000',
            ],
            [
                'name' => 'Mehdi Alami',
                'email' => 'mehdi@ejoutiya.com',
                'phone' => '0655443322',
                'city' => 'Rabat',
                'address' => '88 Avenue Mohammed V',
                'zip_code' => '10000',
            ],
            [
                'name' => 'Sara Bennani',
                'email' => 'sara@ejoutiya.com',
                'phone' => '0644332211',
                'city' => 'Tangier',
                'address' => '12 Rue de Murillo',
                'zip_code' => '90000',
            ],
        ];

        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        foreach ($clients as $clientData) {
            $user = User::updateOrCreate(
                ['email' => $clientData['email']],
                [
                    'name' => $clientData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'client',
                ]
            );

            // Create 3 orders for each client with different statuses
            $statuses = ['pending', 'confirmed', 'delivery', 'rejected'];

            foreach (range(1, 3) as $i) {
                $status = $statuses[array_rand($statuses)];
                
                // Select 1-3 random products for the order
                $orderProducts = $products->random(rand(1, 3));
                $totalPrice = 0;

                $order = Order::create([
                    'client_id' => $user->id,
                    'status' => $status,
                    'total_price' => 0, // Will update after adding items
                    'phone' => $clientData['phone'],
                    'city' => $clientData['city'],
                    'address' => $clientData['address'],
                    'zip_code' => $clientData['zip_code'],
                ]);

                foreach ($orderProducts as $product) {
                    $qty = rand(1, 2);
                    $price = $product->price * $qty;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $product->price,
                    ]);

                    $totalPrice += $price;
                }

                $order->update(['total_price' => $totalPrice]);
            }
        }
    }
}
