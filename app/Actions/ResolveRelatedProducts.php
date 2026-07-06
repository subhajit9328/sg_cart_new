<?php

namespace App\Actions;

use App\Models\Product;

class ResolveRelatedProducts
{
    /**
     * Resolve related product IDs for the given product using the fallback priority:
     *  1. Name matching
     *  2. Category matching
     *  3. Search tag matching
     *
     * Each step fills up to the configured max_no_related_products limit.
     * Subsequent steps only contribute IDs for the remaining slots.
     *
     * @return int[]
     */
    public function handle(Product $product): array
    {
        $max = (int) config('store.max_no_related_products', 4);

        $collected = [];

        // 1. Name matching — seed the list
        $nameIds = $this->resolveByName($product, $collected);
        $collected = array_merge($collected, $nameIds);

        // 2. Category matching — fill remaining slots only
        if (count($collected) < $max) {
            $categoryIds = $this->resolveByCategory($product, $collected);
            $collected = array_merge($collected, $categoryIds);
        }
        // 3. Search tag matching — fill any still-remaining slots
        if (count($collected) < $max) {
            $tagIds = $this->resolveBySearchTag($product, $collected);
            $collected = array_merge($collected, $tagIds);
        }

        return array_slice($collected, 0, $max);
    }

    private function resolveByName(Product $product, array $excludeIds = []): array
    {
        $max = (int) config('store.max_no_related_products', 4);
        $remaining = $max - count($excludeIds);

        if ($remaining <= 0) {
            return [];
        }

        $words = array_filter(
            preg_split('/[\s,]+/', preg_replace('/[^\w\s]/u', '', strtolower($product->name)), -1, PREG_SPLIT_NO_EMPTY),
            fn($word) => strlen($word) >= 3 && !in_array($word, ['for', 'and', 'the', 'with'])
        );

        if (empty($words)) {
            return [];
        }

        $query = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->orWhere('name', 'like', "%{$word}%");
                }
            });

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->limit($remaining)->pluck('id')->toArray();
    }

    private function resolveByCategory(Product $product, array $excludeIds = []): array
    {
        $max = (int) config('store.max_no_related_products', 4);
        $remaining = $max - count($excludeIds);

        if ($remaining <= 0 || !$product->category_id) {
            return [];
        }

        $query = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id);

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->limit($remaining)->pluck('id')->toArray();
    }

    private function resolveBySearchTag(Product $product, array $excludeIds = []): array
    {
        $max = (int) config('store.max_no_related_products', 4);
        $remaining = $max - count($excludeIds);

        $currentTags = $product->searchTerms()->pluck('term')->toArray();

        if ($remaining <= 0 || empty($currentTags)) {
            return [];
        }

        $query = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->whereHas('searchTerms', function ($q) use ($currentTags) {
                $q->whereIn('term', $currentTags);
            });

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->limit($remaining)->pluck('id')->toArray();
    }
}
