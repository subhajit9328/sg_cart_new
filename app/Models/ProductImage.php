<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasUlids;

    protected $fillable = ['product_id', 'path', 'alt', 'is_primary', 'sort_order'];

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
        return ['is_primary' => 'boolean'];
    }

    public function product() { return $this->belongsTo(Product::class); }
}