<?php

namespace SGCart\ImageSearch\Models;

use Illuminate\Database\Eloquent\Model;

class ImageSearchSetting extends Model
{
    protected $table = 'image_search_settings';

    protected $fillable = [
        'provider',
        'model',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
