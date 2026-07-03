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
            self::LOW => 'bg-slate-50 text-slate-600 border border-slate-200',
            self::MEDIUM => 'bg-blue-50 text-blue-700 border border-blue-200',
            self::HIGH => 'bg-amber-50 text-amber-700 border border-amber-200',
            self::URGENT => 'bg-rose-50 text-rose-700 border border-rose-200',
        };
    }
}
