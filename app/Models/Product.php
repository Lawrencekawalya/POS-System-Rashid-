<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'barcode',
        'unit_type',
        'cost_price',
        'selling_price',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * A product has many stock movements.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the current stock level for the product.
     */
    public function currentStock(): int
    {
        return (int) $this->stockMovements()->sum('quantity');
    }

    // Check if the product is low in stock based on a given threshold.
    public function isLowStock(int $threshold = 1): bool
    {
        return $this->currentStock() <= $threshold;
    }
    public function currentStockValue(): int
    {
        return (int) $this->currentStock();
    }
}
