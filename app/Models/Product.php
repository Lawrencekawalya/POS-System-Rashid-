<?php

namespace App\Models;

use App\Services\MenuImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /** Unit type of fresh cuts (meat bought by the kg and sold as portions). */
    public const PORTION = 'portion';

    protected $fillable = [
        'name',
        'brand',
        'barcode',
        'unit_type',
        'portions_per_kg',
        'cost_price',
        'selling_price',
        'is_active',
        'menu_section',
        'image_path',
        'image_credit',
        'image_credit_url',
        'image_is_stock',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'portions_per_kg' => 'decimal:2',
        'image_is_stock' => 'boolean',
    ];

    /**
     * Fresh cuts, managed on the Fresh Cuts page.
     */
    public function scopeFreshCuts($query)
    {
        return $query->where('unit_type', self::PORTION);
    }

    /**
     * Everything except fresh cuts: the items sold and bought as they are.
     */
    public function scopeStockItems($query)
    {
        return $query->where('unit_type', '!=', self::PORTION);
    }

    public function isFreshCut(): bool
    {
        return $this->unit_type === self::PORTION;
    }

    public function freshCutBatches()
    {
        return $this->hasMany(FreshCutBatch::class);
    }

    /**
     * Menu items whose sales use this fresh cut.
     */
    public function portionMenuItems()
    {
        return $this->hasMany(MenuItem::class, 'stock_product_id');
    }

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
    // this is what we will use in the dashboard to show low stock (lowstock) products.
    // We can adjust the threshold as needed.
    public function isLowStock(int $threshold = 1): bool
    {
        return $this->currentStock() <= $threshold;
    }

    public function currentStockValue(): int
    {
        return (int) $this->currentStock();
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'item_id')
            ->where('item_type', 'product');
    }

    public function getImageUrlAttribute(): ?string
    {
        return MenuImageService::url($this->image_path);
    }
}
