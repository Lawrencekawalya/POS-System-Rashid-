<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleRefund extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'amount',
        'refunded_by',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
