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
        'room_id',
        'total_amount',
        'paid_amount',
        'refunded_amount',
        'change_amount',
        'payment_status',
        'payment_method',
        'refunded_at',
        'refunded_by',
        'refund_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'refunded_at' => 'datetime',
        'room_id' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'refunded_at',
    ];

    /**
     * Get the balance remaining for this sale.
     */
    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount - (float) ($this->refunded_amount ?? 0));
    }

    /**
     * A sale has many payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * A sale belongs to a room (optional).
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

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
        return ! is_null($this->refunded_at);
    }

    /**
     * This will handle the partial return items.
     */
    public function refunds()
    {
        return $this->hasMany(SaleRefund::class);
    }

    public function refundedQuantityForSaleItem(int $saleItemId): int
    {
        return (int) $this->refunds()
            ->where('sale_item_id', $saleItemId)
            ->sum('quantity');
    }

    public function isFullyRefunded(): bool
    {
        foreach ($this->items as $item) {
            if ($this->refundedQuantityForSaleItem($item->id) < $item->quantity) {
                return false;
            }
        }

        return true;
    }
}
