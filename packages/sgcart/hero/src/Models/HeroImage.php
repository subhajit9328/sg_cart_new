<?php

namespace SGCart\Hero\Models;

use Illuminate\Database\Eloquent\Model;

class HeroImage extends Model
{
    protected $table = 'hero_images';

    protected $fillable = ['image_path', 'sort_order'];
}
