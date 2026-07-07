<?php

namespace SGCart\Reviews\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewStatus extends Model
{
    protected $table = 'review_statuses';

    protected $fillable = ['name'];

    public function reviews()
    {
        return $this->hasMany(Review::class, 'status_id');
    }
}
