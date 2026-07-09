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
            self::OPEN => 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700',
            self::REVIEW => 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700',
            self::RESOLVE => 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700',
            self::REJECT => 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700',
        };
    }
}
