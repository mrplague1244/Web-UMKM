<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'category',
        'address',
        'notes',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
