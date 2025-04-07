<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        //
        Schema::defaultStringLength(191);
        if (env('APP_ENV') !== 'local') {
            $url->forceScheme('https');
        }

        // Gate::define('fully-verified', function (User $user) {
        //     return $user->is_verified && $user->verifDetails->status === 'approved';
        // });

        // Create categories on first boot if they don't exist
        // if (Category::count() === 0) {
        //     $categories = [
        //         [
        //             'category' => 'Electronics',
        //             'description' => 'Electronic devices and accessories',
        //             'category_image' => 'electronics.jpg'
        //         ],
        //         [
        //             'category' => 'Printed Materials',
        //             'description' => 'Books, documents, and other printed resources',
        //             'category_image' => 'printed_materials.jpg'
        //         ],
        //         [
        //             'category' => 'Stationeries',
        //             'description' => 'Office and school supplies',
        //             'category_image' => 'stationeries.jpg'
        //         ],
        //         [
        //             'category' => 'Uniforms',
        //             'description' => 'Official uniforms and dress codes',
        //             'category_image' => 'uniform.jpg'
        //         ],
        //         [
        //             'category' => 'Medical Supplies',
        //             'description' => 'Medical equipment and healthcare supplies',
        //             'category_image' => 'medical-supplies.jpg'
        //         ],
        //         [
        //             'category' => 'Culinary Supplies',
        //             'description' => 'Kitchen/Culinary equipment and supplies',
        //             'category_image' => 'culinary-supplies.jpg'
        //         ]
        //     ];

        //     Category::insert($categories);
        // }
    }
}
