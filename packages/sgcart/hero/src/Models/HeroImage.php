<?php

namespace SGCart\Hero\Models;

use Illuminate\Database\Eloquent\Model;

class HeroImage extends Model
{
    protected $table = 'hero_images';

    protected $fillable = ['image_path', 'image_desktop', 'image_tablet', 'image_mobile', 'url', 'sort_order'];
}
