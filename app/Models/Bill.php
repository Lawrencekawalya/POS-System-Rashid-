<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'room_id',
        'status',
        'total',
        'payment_type',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    // Get total dynamically (safe)
    public function calculateTotal()
    {
        return $this->items()->sum('subtotal');
    }

    // Scope: open bills
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function recalculateTotal()
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }
}
