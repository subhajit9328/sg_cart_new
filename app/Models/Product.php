<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Traits\HasSearchTerms;

class Product extends Model
{
    use SoftDeletes, HasUlids, HasSearchTerms;

    protected $fillable = [
        'name', 'slug', 'sku', 'category_id', 'manufacturer_id',
        'short_description', 'description', 'price', 'sale_price',
        'stock', 'min_stock', 'status', 'rejection_reason', 'seller_note', 'weight', 'dimensions',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            if (empty($product->slug) || ($product->isDirty('name') && $product->isClean('slug'))) {
                $slug = \Illuminate\Support\Str::slug($product->name);
                
                $originalSlug = $slug;
                $counter = 1;
                
                while (static::where('slug', $slug)
                    ->where('id', '!=', $product->id ?? 0)
                    ->exists()
                ) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                
                $product->slug = $slug;
            }
        });

        static::created(function ($product) {
            try {
                if (class_exists(\App\Services\SearchTagGenerator::class)) {
                    $tags = \App\Services\SearchTagGenerator::generate($product);
                    foreach ($tags as $tag) {
                        $product->searchTerms()->updateOrCreate([
                            'term' => strtolower(trim($tag))
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Failed to auto-generate search tags for product ID {$product->id}: " . $e->getMessage());
            }
        });
    }

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

    /**
     * Resolve the route binding to support both ULID and standard auto-increment integer ID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->first() ?? abort(404);
        }
        return $this->where($field ?? 'ulid', $value)->first() ?? abort(404);
    }

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'sale_price'  => 'decimal:2',
            'status'      => \App\Enums\ProductStatus::class,
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
    
    public function seller()
    {
        if (class_exists(\SGCart\Marketplace\Models\Seller::class)) {
            return $this->belongsTo(\SGCart\Marketplace\Models\Seller::class, 'seller_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function defaultImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }

    public function variants()
    {
        if (class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            return $this->hasMany(\SGCart\ProductVariants\Models\ProductVariant::class);
        }
        return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
    }

    public function relatedProducts()
    {
        if (class_exists(\SGCart\RelatedProducts\Models\RelatedProduct::class)) {
            return $this->belongsToMany(self::class, 'related_products', 'product_id', 'related_id');
        }
        return $this->belongsToMany(self::class, 'products', 'id', 'id')->whereRaw('1 = 0');
    }
}