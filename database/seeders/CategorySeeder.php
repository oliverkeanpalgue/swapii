<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'category' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'category_image' => 'electronics.jpg',
            ],
            [
                'category' => 'Printed Materials',
                'description' => 'Books, documents, and other printed resources',
                'category_image' => 'printed_materials.jpg',
            ],
            [
                'category' => 'Stationeries',
                'description' => 'Office and school supplies',
                'category_image' => 'stationeries.jpg',
            ],
            [
                'category' => 'Uniforms',
                'description' => 'Official uniforms and dress codes',
                'category_image' => 'uniform.jpg',
            ],
            [
                'category' => 'Medical Supplies',
                'description' => 'Medical equipment and healthcare supplies',
                'category_image' => 'medical-supplies.jpg',
            ],
            [
                'category' => 'Culinary Supplies',
                'description' => 'Kitchen/Culinary equipment and supplies',
                'category_image' => 'culinary-supplies.jpg',
            ],
        ]);
    }
}
