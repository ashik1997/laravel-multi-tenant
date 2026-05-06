<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => 'Laptop',
                'price' => 50000,
            ],
            [
                'name' => 'Rice',
                'price' => 60,
            ],
        ]);
    }
}
