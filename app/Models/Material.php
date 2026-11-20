<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit',
        'stock',
        'min_stock',
        'cost_per_unit',
        'is_active',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
