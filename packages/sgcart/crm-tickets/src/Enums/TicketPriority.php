<?php

namespace SGCart\CrmTickets\Enums;

enum TicketPriority: string
{
    case LOW = 'Low';
    case MEDIUM = 'Medium';
    case HIGH = 'High';
    case URGENT = 'Urgent';

    /**
     * Get the badge CSS classes for admin display.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::LOW => 'bg-slate-50 text-slate-600 border border-slate-200 dark:bg-slate-900/30 dark:text-slate-300 dark:border-slate-700',
            self::MEDIUM => 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700',
            self::HIGH => 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700',
            self::URGENT => 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700',
        };
    }
}
