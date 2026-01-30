<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /**
     * Disable updated_at (immutable record).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'supplier_name',
        'reference_no',
        'total_cost',
        'purchased_at',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    /**
     * Who recorded the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Purchase line items.
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
