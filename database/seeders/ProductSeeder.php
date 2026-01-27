<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductStock; 
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'name' => "Dummy Shirt $i",
                'slug' => Str::slug("dummy-shirt-$i"),
                'category' => "Category " . rand(1,5),
                'price' => rand(100000, 500000),
                'original_price' => rand(500000, 1000000),
                'video_url' => "https://www.example.com/video$i.mp4",
                'images' => [
                    "https://via.placeholder.com/500x500?text=Product+$i+Image1",
                    "https://via.placeholder.com/500x500?text=Product+$i+Image2"
                ],
                'colors' => ['Red', 'Blue', 'Black'],
                'badges' => ['New', 'Hot'],
                'description' => "This is a dummy description for product $i.",
                'size_guide_desc' => "Standard size guide applies.",
                'shipping_info' => [
                    'weight' => rand(100, 500) . 'g',
                    'origin' => 'Jakarta, Indonesia',
                    'delivery_time' => rand(1, 7) . ' days',
                ],
                'trust_badges' => ['Original', 'Warranty'],
                'size_guide' => [
                    'S' => 'Chest 90cm, Length 65cm',
                    'M' => 'Chest 95cm, Length 67cm',
                    'L' => 'Chest 100cm, Length 69cm',
                    'XL' => 'Chest 105cm, Length 71cm',
                ],
                'description_video_url' => "https://www.example.com/desc-video$i.mp4",
                'detail_product' => "Details about Dummy Product $i.",
                'detail_images' => [
                    "https://via.placeholder.com/600x400?text=Detail+$i+1",
                    "https://via.placeholder.com/600x400?text=Detail+$i+2",
                ],
                'lifestyle_images' => [
                    "https://via.placeholder.com/800x600?text=Lifestyle+$i+1",
                    "https://via.placeholder.com/800x600?text=Lifestyle+$i+2",
                ],
                'related_products' => [],
                'is_active' => true,
            ]);

            $sizes = ['S', 'M', 'L', 'XL'];
            foreach ($sizes as $size) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'stock' => rand(5, 50)
                ]);
            }
        }
    }
}
