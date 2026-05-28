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
        return $this->hasMany(Sale::class)->where('payment_status', '!=', 'paid');
    }

    /**
     * Calculate current debt/balance for the room.
     */
    public function currentBalance(): float
    {
        $totalSales = $this->sales()->sum('total_amount');
        $totalPayments = $this->hasMany(Payment::class)->sum('amount');
        
        return (float) ($totalSales - $totalPayments);
    }

    public function activeBill()
    {
        return $this->hasOne(Bill::class)->where('status', 'open');
    }
}
