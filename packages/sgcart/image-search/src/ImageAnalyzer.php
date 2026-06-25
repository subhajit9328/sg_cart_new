<?php

namespace SGCart\ImageSearch;

use Illuminate\Http\UploadedFile;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Promptable;

class ImageAnalyzer implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are an image analysis assistant.';
    }

    /**
     * Analyze image and extract descriptive search terms.
     */
    public function analyze(mixed $image, ?string $provider = null, ?string $model = null): array
    {
        $attachment = $this->resolveImageAttachment($image);

        $prompt = 'Identify the main product in this image and output a clean JSON array containing only the key search terms (like brand, color, material, product type) suitable for database search.
Do NOT include conversational text, markdown formatting, or code blocks.
If you cannot identify any product, return an empty JSON array [].
Example output:
["blue", "denim", "jacket"]';

        $provider = $provider ?: config('image-search.image_analyzer_provider') ?: config('ai.default');
        $model = $model ?: config('image-search.image_analyzer_model');

        $response = $this->prompt($prompt, [$attachment], $provider, $model);
        $text = trim($response->text);

        // Try to find a JSON array in the text response using regex
        if (preg_match('/\[\s*.*?\s*\]/s', $text, $matches)) {
            $jsonText = $matches[0];
            $terms = json_decode($jsonText, true);
            if (is_array($terms)) {
                $terms = array_filter(array_map(fn($t) => strtolower(trim($t)), $terms));
                return array_values(array_unique($terms));
            }
        }

        // Fallback: split by whitespace if JSON decoding is not found or fails
        $normalizedText = strtolower($text);
        $cleanText = preg_replace('/[^\w\s]/u', ' ', $normalizedText);
        $words = preg_split('/\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($words));
    }

    /**
     * Resolve mixed image input to an AI attachment.
     */
    protected function resolveImageAttachment(mixed $image): mixed
    {
        if ($image instanceof UploadedFile) {
            return Image::fromUpload($image);
        }

        if (is_string($image)) {
            // Check if it's base64 encoded image
            if (preg_match('/^data:image\/(\w+);base64,/', $image)) {
                $base64 = substr($image, strpos($image, ',') + 1);
                $mimeType = null;
                if (preg_match('/^data:(image\/[\w\-+.]+);base64,/', $image, $matches)) {
                    $mimeType = $matches[1];
                }

                return Image::fromBase64($base64, $mimeType);
            }

            // Check if it is a local file path that already exists
            if (file_exists($image)) {
                return Image::fromPath($image);
            }

            // Check if it looks like a URL
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return Image::fromUrl($image);
            }

            // Treat it as raw base64 data without prefix
            return Image::fromBase64($image);
        }

        // Already an AI attachment
        return $image;
    }
}
