<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'business_date',
        'user_id',
        'cash_expected',
        'cash_counted',
        'difference',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

