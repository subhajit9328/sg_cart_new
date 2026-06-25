<?php

namespace SGCart\ImageSearch\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use SGCart\ImageSearch\Models\SearchTerm;

trait HasSearchTerms
{
    /**
     * Get all of the model's search terms.
     */
    public function searchTerms(): MorphMany
    {
        return $this->morphMany(SearchTerm::class, 'searchable');
    }
}
