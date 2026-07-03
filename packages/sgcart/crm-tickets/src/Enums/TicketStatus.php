<?php

namespace SGCart\CrmTickets\Enums;

enum TicketStatus: string
{
    case OPEN = 'Open';
    case REVIEW = 'Review';
    case RESOLVE = 'Resolve';
    case REJECT = 'Reject';

    public static function fromDb(string $name): self
    {
        return match ($name) {
            'Open' => self::OPEN,
            'Review' => self::REVIEW,
            'Resolve' => self::RESOLVE,
            'Reject' => self::REJECT,
        };
    }

    /**
     * Get the badge CSS classes for admin display.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::OPEN => 'bg-blue-50 text-blue-700 border border-blue-200',
            self::REVIEW => 'bg-amber-50 text-amber-700 border border-amber-200',
            self::RESOLVE => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            self::REJECT => 'bg-rose-50 text-rose-700 border border-rose-200',
        };
    }
}
