<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * Disable updated_at timestamp.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'total_amount',
        'paid_amount',
        'change_amount',
        'refunded_at',
        'refunded_by',
        'refund_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'refunded_at',
    ];

    /**
     * A sale belongs to a cashier (user).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A sale has many sale items.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * This will handle the returned items.
     */
    public function isRefunded(): bool
    {
        return !is_null($this->refunded_at);
    }

    /**
     * This will handle the partial return items.
     */
    public function refunds()
    {
        return $this->hasMany(SaleRefund::class);
    }
    public function refundedQuantityFor(int $productId): int
    {
        return (int) $this->refunds()
            ->where('product_id', $productId)
            ->sum('quantity');
    }
    public function isFullyRefunded(): bool
    {
        foreach ($this->items as $item) {
            if ($this->refundedQuantityFor($item->product_id) < $item->quantity) {
                return false;
            }
        }
        return true;
    }
}
