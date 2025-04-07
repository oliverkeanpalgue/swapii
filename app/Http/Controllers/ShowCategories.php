<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class showCategories extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Category::all();

    }
}
