<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /**
     * Disable updated_at timestamp.
     */
    const UPDATED_AT = null;

    /** reference_type for portions added by a fresh-cut purchase. */
    public const FRESH_CUT_BATCH = 'fresh_cut_batch';

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
