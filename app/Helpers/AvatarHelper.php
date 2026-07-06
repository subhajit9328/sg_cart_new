<?php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Get initials from a name by filtering out common stop-words and symbols.
     * For example, "H&M" becomes "HM", "Home & Kitchen" becomes "HK", etc.
     *
     * @param string $name
     * @return string
     */
    public static function getInitials(string $name): string
    {
        $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $name);
        $words = preg_split('/\s+/', trim($cleanName));
        $ignoredWords = ['and', 'or', 'of', 'in', 'to', 'for', 'with', 'the', 'a', 'an'];
        
        $filteredWords = array_values(array_filter($words, function ($word) use ($ignoredWords) {
            return !in_array(strtolower(trim($word)), $ignoredWords);
        }));

        if (count($filteredWords) >= 2) {
            return strtoupper(substr($filteredWords[0], 0, 1) . substr($filteredWords[1], 0, 1));
        } elseif (count($filteredWords) === 1) {
            return strtoupper(substr($filteredWords[0], 0, 2));
        }

        return strtoupper(substr($name, 0, 2));
    }
}
