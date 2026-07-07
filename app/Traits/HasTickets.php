<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTickets
{
    /**
     * Get all tickets linked to this model (polymorphic).
     */
    public function tickets(): MorphMany
    {
        if (class_exists(\SGCart\CrmTickets\Models\Ticket::class)) {
            return $this->morphMany(\SGCart\CrmTickets\Models\Ticket::class, 'ticketable');
        }

        // Fallback: Return a dummy MorphMany relationship pointing to self
        // but constrained to return an empty collection so that it is safe
        // even if the package is removed from the codebase.
        return $this->morphMany(self::class, 'ticketable')->whereRaw('1 = 0');
    }
}