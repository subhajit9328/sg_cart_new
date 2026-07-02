<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SearchTagGenerator
{
    /**
     * Generate search tags for a product.
     *
     * @param Product $product
     * @return array
     */
    public static function generate(Product $product): array
    {
        $tags = [];

        // 1. Gather inputs
        $name = strtolower($product->name);
        $description = strtolower(strip_tags($product->description ?? $product->short_description ?? ''));
        
        $categories = [];
        if ($product->category) {
            $cat = $product->category;
            $categories[] = strtolower($cat->name);
            while ($cat->parent) {
                $cat = $cat->parent;
                $categories[] = strtolower($cat->name);
            }
        }

        // 2. Clean input
        // Remove punctuation and special characters from name but keep letters/numbers and spaces
        $cleanName = preg_replace('/[^\w\s-]/u', '', $name);
        $words = preg_split('/[\s,]+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY);

        // 3. Define lists of colors, nouns, and audiences to build smart combinations
        $colorsList = [
            'blue', 'navy blue', 'navy', 'black', 'white', 'grey', 'gray', 'red', 'green', 
            'teal', 'pink', 'peach', 'yellow', 'orange', 'brown', 'purple', 'violet', 
            'maroon', 'beige', 'olive', 'khaki', 'silver', 'gold', 'mustard', 'multicolor'
        ];

        $nounsList = [
            'shirt', 'shirts', 't-shirt', 'tshirts', 'tshirt', 'tee', 'jeans', 'pant', 'pants', 
            'trouser', 'trousers', 'hoodie', 'hoodies', 'sweatshirt', 'sweatshirts', 
            'dress', 'dresses', 'wrap', 'kurta', 'kurtas', 'kurti', 'kurtis', 'sari', 'saris', 
            'saree', 'sarees', 'blazer', 'blazers', 'jacket', 'jackets', 'shoe', 'shoes', 
            'sneaker', 'sneakers', 'footwear', 'bag', 'bags', 'sunglasses', 'eyewear'
        ];

        $audiencesList = [
            'men' => ['men', 'man', 'male', 'gent', 'gents'],
            'women' => ['women', 'woman', 'female', 'lady', 'ladies', 'girl', 'girls'],
            'kids' => ['kids', 'kid', 'child', 'children', 'boy', 'boys', 'girl', 'girls', 'baby', 'toddler', 'toddlers']
        ];

        // 4. Identify present colors, nouns, and audiences in the product name
        $presentColors = [];
        foreach ($colorsList as $color) {
            if (str_contains($cleanName, $color)) {
                $presentColors[] = $color;
            }
        }

        $presentNouns = [];
        foreach ($nounsList as $noun) {
            // Match whole word for nouns to avoid partial matches
            if (preg_match('/\b' . preg_quote($noun, '/') . '\b/', $cleanName)) {
                $presentNouns[] = $noun;
            }
        }

        $presentAudiences = [];
        foreach ($audiencesList as $key => $syns) {
            foreach ($syns as $syn) {
                if (preg_match('/\b' . preg_quote($syn, '/') . '\b/', $cleanName)) {
                    $presentAudiences[$key] = $key;
                    break;
                }
            }
        }

        // 5. Build standard combinations
        // A. Add product name parts
        // Let's add the category names
        foreach ($categories as $catName) {
            $tags[] = $catName;
            $tags[] = str_replace(["'s", 's\''], '', $catName);
        }

        // B. Add base colors and nouns
        foreach ($presentColors as $color) {
            $tags[] = $color;
        }
        foreach ($presentNouns as $noun) {
            $tags[] = $noun;
            $tags[] = self::getPluralSingularAlternative($noun);
        }

        // C. Combine Color + Noun (e.g., "navy blue shirt")
        foreach ($presentColors as $color) {
            foreach ($presentNouns as $noun) {
                $tags[] = "$color $noun";
                $tags[] = "$color " . self::getPluralSingularAlternative($noun);
            }
        }

        // D. Combine Noun + Audience (e.g., "shirt for men", "shirt men")
        foreach ($presentNouns as $noun) {
            $nounPlur = self::getPluralSingularAlternative($noun);
            foreach ($presentAudiences as $aud) {
                $audSingular = self::getAudienceSingular($aud);
                $audPlural = self::getAudiencePlural($aud);

                $tags[] = "$noun for $audSingular";
                $tags[] = "$noun for $audPlural";
                $tags[] = "$nounPlur for $audSingular";
                $tags[] = "$nounPlur for $audPlural";
                
                $tags[] = "$noun $audSingular";
                $tags[] = "$noun $audPlural";
                $tags[] = "$nounPlur $audSingular";
                $tags[] = "$nounPlur $audPlural";
            }
        }

        // E. Combine Color + Noun + Audience (e.g., "navy blue shirt for kids", "navy blue shirt men")
        foreach ($presentColors as $color) {
            foreach ($presentNouns as $noun) {
                $nounPlur = self::getPluralSingularAlternative($noun);
                foreach ($presentAudiences as $aud) {
                    $audSingular = self::getAudienceSingular($aud);
                    $audPlural = self::getAudiencePlural($aud);

                    $tags[] = "$color $noun for $audSingular";
                    $tags[] = "$color $noun for $audPlural";
                    $tags[] = "$color $noun $audSingular";
                    $tags[] = "$color $noun $audPlural";
                    
                    $tags[] = "$color $nounPlur for $audSingular";
                    $tags[] = "$color $nounPlur for $audPlural";
                    $tags[] = "$color $nounPlur $audSingular";
                    $tags[] = "$color $nounPlur $audPlural";
                }
            }
        }

        // F. Extract individual words of 3+ letters and add them
        foreach ($words as $word) {
            if (strlen($word) >= 3 && !in_array($word, ['for', 'and', 'the', 'with', 'kids', 'gents'])) {
                $tags[] = $word;
            }
        }

        // Add some basic bigrams from name if they make sense
        for ($i = 0; $i < count($words) - 1; $i++) {
            $bigram = $words[$i] . ' ' . $words[$i+1];
            if (!in_array($words[$i], ['for', 'and', 'the', 'with']) && !in_array($words[$i+1], ['for', 'and', 'the', 'with'])) {
                $tags[] = $bigram;
            }
        }

        // G. Add description keywords (words of 4+ letters that appear frequently)
        $descClean = preg_replace('/[^\w\s]/u', ' ', $description);
        $descWords = preg_split('/[\s,]+/', $descClean, -1, PREG_SPLIT_NO_EMPTY);
        $wordCounts = array_count_values(array_filter($descWords, function($w) {
            return strlen($w) >= 4 && !in_array($w, ['with', 'this', 'that', 'from', 'have', 'your', 'their']);
        }));
        arsort($wordCounts);
        $topDescWords = array_slice(array_keys($wordCounts), 0, 5);
        foreach ($topDescWords as $tdw) {
            $tags[] = $tdw;
        }

        // Clean up: lowercase, trim, remove duplicate values, filter out empty, reset index
        $tags = array_map('trim', $tags);
        $tags = array_map('strtolower', $tags);
        $tags = array_unique(array_filter($tags, function($val) {
            return !empty($val) && strlen($val) >= 2;
        }));

        return array_values($tags);
    }

    private static function getPluralSingularAlternative(string $noun): string
    {
        if (str_ends_with($noun, 'ies')) {
            return substr($noun, 0, -3) . 'y';
        }
        if (str_ends_with($noun, 'es') && !str_ends_with($noun, 's')) {
            return substr($noun, 0, -2);
        }
        if (str_ends_with($noun, 'ss')) {
            return $noun . 'es';
        }
        if (str_ends_with($noun, 's')) {
            return substr($noun, 0, -1);
        }
        return $noun . 's';
    }

    private static function getAudienceSingular(string $aud): string
    {
        switch ($aud) {
            case 'men': return 'men';
            case 'women': return 'women';
            case 'kids': return 'kid';
            default: return $aud;
        }
    }

    private static function getAudiencePlural(string $aud): string
    {
        switch ($aud) {
            case 'men': return 'men';
            case 'women': return 'women';
            case 'kids': return 'kids';
            default: return $aud;
        }
    }
}
