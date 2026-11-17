<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name'];

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function unpaidSales()
    {
        return $this->hasMany(Sale::class)->whereNotIn('payment_status', ['paid', 'refunded']);
    }

    /**
     * Calculate current debt/balance for the room.
     */
    public function currentBalance(): float
    {
        // Refund payouts are recorded as payments, so a refunded sale must not
        // also count as a negative balance (credit) on the room.
        return (float) $this->unpaidSales()->get()->sum(fn (Sale $sale) => $sale->balance);
    }

    public function activeBill()
    {
        return $this->hasOne(Bill::class)->where('status', 'open');
    }
}
