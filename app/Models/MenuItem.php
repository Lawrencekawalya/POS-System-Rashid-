<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'price',
        'category',
        'stock_product_id',
        'stock_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_product_id' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'item_id')
            ->where('item_type', 'menu');
    }

    public function stockProduct()
    {
        return $this->belongsTo(Product::class, 'stock_product_id');
    }
}
