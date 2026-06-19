<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'sku', 'category_id', 'manufacturer_id',
        'short_description', 'description', 'price', 'sale_price',
        'stock', 'status', 'is_featured', 'weight', 'dimensions',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'sale_price'  => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function category()    { return $this->belongsTo(Category::class); }
    public function manufacturer(){ return $this->belongsTo(Manufacturer::class); }
    public function variants()    { return $this->hasMany(ProductVariant::class); }
    public function images()      { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function primaryImage() { return $this->hasOne(ProductImage::class)->where('is_primary', true); }
    public function specifications(){ return $this->hasMany(ProductSpecification::class)->orderBy('sort_order'); }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->slug ??= Str::slug($m->name));
    }
}