<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSearchTerms
{
    /**
     * Get all of the model's search terms.
     */
    public function searchTerms(): MorphMany
    {
        if (class_exists(\SGCart\ImageSearch\Models\SearchTerm::class)) {
            return $this->morphMany(\SGCart\ImageSearch\Models\SearchTerm::class, 'searchable');
        }

        // Fallback: Return a dummy MorphMany relationship pointing to self
        // but constrained to return an empty collection so that it is safe
        // even if the package is removed from the codebase.
        return $this->morphMany(self::class, 'searchable')->whereRaw('1 = 0');
    }
}