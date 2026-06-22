<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes, HasUlids;

    protected $fillable = [
        'name', 'sku', 'category_id', 'manufacturer_id',
        'short_description', 'description', 'price', 'sale_price',
        'stock', 'status', 'weight', 'dimensions',
    ];

    /**
     * Only auto-generate ULID for the `ulid` column.
     * The `id` column remains a standard auto-increment integer PK.
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * The column used for route model binding (exposes ULID, not integer id).
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'sale_price'  => 'decimal:2',
        ];
    }

    /**
     * Fallback accessor to dynamically retrieve default or first image.
     */
    public function getImageAttribute()
    {
        $default = $this->images->firstWhere('is_default', true);
        return $default ? $default->image_path : ($this->images->first()?->image_path);
    }

    public function category()    { return $this->belongsTo(Category::class); }
    public function manufacturer(){ return $this->belongsTo(Manufacturer::class); }
    
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function defaultImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }
}