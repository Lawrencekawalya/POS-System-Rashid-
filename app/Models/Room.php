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
        return (float) $this->sales()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount - refunded_amount), 0) as balance')
            ->value('balance');
    }

    public function activeBill()
    {
        return $this->hasOne(Bill::class)->where('status', 'open');
    }
}
