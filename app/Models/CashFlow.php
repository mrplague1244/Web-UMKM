<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', // in, out
        'category', // sales, restock, operational, electricity, etc.
        'amount',
        'description',
        'transaction_id',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
