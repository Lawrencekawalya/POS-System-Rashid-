<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One purchase of a fresh cut, e.g. 5 kg of beef at 8 portions per kg.
 */
class FreshCutBatch extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'kg',
        'portions_per_kg',
        'portions',
        'total_cost',
        'supplier',
        'purchased_at',
    ];

    protected $casts = [
        'kg' => 'decimal:2',
        'portions_per_kg' => 'decimal:2',
        'portions' => 'integer',
        'total_cost' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stockMovement()
    {
        return $this->hasOne(StockMovement::class, 'reference_id')
            ->where('reference_type', StockMovement::FRESH_CUT_BATCH);
    }
}
