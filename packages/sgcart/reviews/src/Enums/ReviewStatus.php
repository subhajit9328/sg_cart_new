<?php

namespace SGCart\Reviews\Enums;

enum ReviewStatus: string
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';

    public static function fromDb(string $name): ReviewStatus
    {
        return match ($name) {
            'Pending' => self::PENDING,
            'Approved' => self::APPROVED,
            'Rejected' => self::REJECTED,
        };
    }
}
