<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code',
        'customer_id',
        'total_amount',
        'total_cost',
        'gross_profit',
        'payment_method',
        'payment_status',
        'claim_token',
        'claim_status',
        'claimed_at',
        'potential_points',
        'cashier_name',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'claimed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function cashFlow()
    {
        return $this->hasOne(CashFlow::class);
    }
}
