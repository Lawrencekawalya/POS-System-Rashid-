<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $lowStockProducts = Product::all()
                ->filter(fn($product) => $product->isLowStock())
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock' => $product->currentStock(),
                    ];
                });

            $view->with('lowStockProducts', $lowStockProducts);
        });
    }
}
