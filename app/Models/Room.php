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
        $balance = $this->sales()
            ->selectRaw('COALESCE(SUM(total_amount - COALESCE(paid_amount, 0) - COALESCE(refunded_amount, 0)), 0) as room_balance')
            ->first()
            ?->room_balance;

        return (float) $balance;
    }

    public function activeBill()
    {
        return $this->hasOne(Bill::class)->where('status', 'open');
    }
}
