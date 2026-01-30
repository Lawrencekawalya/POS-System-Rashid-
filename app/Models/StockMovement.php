<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /**
     * Disable updated_at timestamp.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
        'remarks',
    ];

    /**
     * A stock movement belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
