<?php

namespace App\Models;

use App\Enums\SMSStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SMSRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number', 
        'message', 
        'status'
    ];

    /**
     * Cast the `status` attribute to the SMSStatus Enum.
     */
    protected $casts = [
        'status' => SMSStatus::class,
    ];

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, SMSStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }
}
