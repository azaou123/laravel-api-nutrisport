<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::all();

        $products = [
            ['name' => 'Whey Protein', 'stock' => 100],
            ['name' => 'Creatine', 'stock' => 80],
            ['name' => 'BCAA', 'stock' => 60],
        ];

        foreach ($products as $p) {
            $product = Product::create($p);

            foreach ($sites as $site) {
                DB::table('product_site')->insert([
                    'product_id' => $product->id,
                    'site_id' => $site->id,
                    'price' => rand(20, 50)
                ]);
            }
        }
    }
}

