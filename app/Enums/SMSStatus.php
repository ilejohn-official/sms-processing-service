<?php

namespace App\Enums;

enum SMSStatus: string
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    /**
     * Get all available statuses.
     *
     * @return array
     */
    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
