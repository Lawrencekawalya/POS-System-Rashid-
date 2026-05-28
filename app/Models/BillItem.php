<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'item_type',
        'item_id',
        'quantity',
        'price',
        'subtotal',
        'source',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    // Dynamic relationship (important for mixed items)
    public function item()
    {
        if ($this->item_type === 'product') {
            return $this->belongsTo(Product::class, 'item_id');
        }

        if ($this->item_type === 'menu') {
            return $this->belongsTo(MenuItem::class, 'item_id');
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function calculateSubtotal()
    {
        return $this->quantity * $this->price;
    }
}
