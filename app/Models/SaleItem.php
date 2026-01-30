<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    /**
     * Disable updated_at timestamp.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * A sale item belongs to a sale.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * A sale item belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
