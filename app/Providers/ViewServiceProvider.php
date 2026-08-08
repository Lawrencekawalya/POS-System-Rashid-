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

            $lowStockProducts = Product::where('is_active', true)
                ->with('stockMovements')
                ->get()
                ->filter(fn (Product $product) => $product->isLowStock(5))
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock' => $product->currentStock(),
                    ];
                })
                ->sortBy('stock')
                ->take(5)
                ->values();

            $view->with('lowStockProducts', $lowStockProducts);
        });
    }
}
