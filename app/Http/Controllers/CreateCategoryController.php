<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CreateCategoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (Category::count() === 0) {
            $categories = [
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
            ];

            Category::insert($categories);
        }
    }
}
