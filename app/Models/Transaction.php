<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Comision rate in percent
     */
    private const COMMISSION_RATE = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount',
        'profit',
    ];

    /**
     * Automatically cast attributes to their appropriate types.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    /**
     * Calculate profit for a transaction.
     */
    public static function calculateProfit(float $amount): float
    {
        return $amount * self::COMMISSION_RATE / 100; 
    }

}
