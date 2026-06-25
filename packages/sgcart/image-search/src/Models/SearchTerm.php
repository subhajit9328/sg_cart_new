<?php

namespace SGCart\ImageSearch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SearchTerm extends Model
{
    protected $fillable = ['term', 'searchable_type', 'searchable_id'];

    /**
     * Get the parent searchable model.
     */
    public function searchable(): MorphTo
    {
        return $this->morphTo();
    }
}
