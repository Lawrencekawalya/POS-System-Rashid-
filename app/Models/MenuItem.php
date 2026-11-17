<?php

namespace App\Models;

use App\Services\MenuImageService;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'price',
        'category',
        'stock_product_id',
        'stock_quantity',
        'description',
        'show_on_menu',
        'sort_order',
        'image_path',
        'image_credit',
        'image_credit_url',
        'image_is_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_product_id' => 'integer',
        'stock_quantity' => 'integer',
        'show_on_menu' => 'boolean',
        'image_is_stock' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // New items go to the end of their section on the guest menu.
        static::creating(function (MenuItem $item) {
            if (! $item->sort_order) {
                $item->sort_order = (int) static::max('sort_order') + 1;
            }
        });
    }

    public function scopeOnGuestMenu($query)
    {
        return $query->where('show_on_menu', true)->orderBy('sort_order')->orderBy('name');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'item_id')
            ->where('item_type', 'menu');
    }

    public function stockProduct()
    {
        return $this->belongsTo(Product::class, 'stock_product_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return MenuImageService::url($this->image_path);
    }
}
