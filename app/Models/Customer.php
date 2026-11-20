<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'total_points',
        'tier',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function loyaltyLogs()
    {
        return $this->hasMany(LoyaltyLog::class);
    }

    /**
     * Hitung tier membership berdasarkan total akumulasi poin
     */
    public function updateTier(): void
    {
        $tiers = config('beverage.loyalty.tiers', [
            'VIP' => 50,
            'Gold' => 20,
            'Silver' => 5,
            'Bronze' => 0,
        ]);

        foreach ($tiers as $tierName => $minPoints) {
            if ($this->total_points >= $minPoints) {
                $this->tier = $tierName;
                break;
            }
        }

        $this->save();
    }
}
