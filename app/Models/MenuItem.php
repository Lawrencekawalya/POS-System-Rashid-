<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'price',
        'category',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'item_id')
            ->where('item_type', 'menu');
    }
}
