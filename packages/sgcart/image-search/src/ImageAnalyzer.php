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

        $categoryList = 'Shirts, T-Shirts & Polos, Jeans & Trousers, Kurta & Ethnic, Dresses, Bags & Handbags, Footwear, Sunglasses, etc.';
        if (class_exists(\App\Models\Category::class)) {
            $names = \App\Models\Category::pluck('name')->unique()->toArray();
            if (!empty($names)) {
                $categoryList = implode(', ', $names);
            }
        }

        $prompt = 'Identify the main product in this image and output a clean JSON object in this format:
{
  "object_type": "Must be one of the categories from this list: ' . $categoryList . '",
  "gender": "male" or "female" or "kid" (ONLY specify gender if a person is shown wearing or presenting the product in the image. If there is no person in the image, you MUST return null for gender),
  "color": "the primary color of the product (e.g. blue, red, black, light blue)",
  "keywords": ["list", "of", "relevant", "keywords", "such as material type, dress type (e.g. shirt, kurty, jeans, jacket), style, etc. Do NOT include the color here as it is captured in color key."]
}

Do NOT include any conversational text, markdown formatting, or code blocks.
If you cannot identify any product, return:
{
  "object_type": null,
  "gender": null,
  "color": null,
  "keywords": []
}';

        $provider = $provider ?: config('image-search.image_analyzer_provider') ?: config('ai.default');
        $model = $model ?: config('image-search.image_analyzer_model');

        $response = $this->prompt($prompt, [$attachment], $provider, $model);
        $text = trim($response->text);

        // Try to find a JSON object in the text response using regex
        if (preg_match('/\{[^\}]*\}/s', $text, $matches)) {
            $jsonText = $matches[0];
            $data = json_decode($jsonText, true);
            if (is_array($data)) {
                $keywords = $data['keywords'] ?? [];
                if (is_string($keywords)) {
                    $keywords = array_map('trim', explode(',', $keywords));
                }
                return [
                    'object_type' => $data['object_type'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'color' => $data['color'] ?? null,
                    'keywords' => is_array($keywords)
                        ? array_values(array_unique(array_filter(array_map(fn($t) => strtolower(trim($t)), $keywords))))
                        : []
                ];
            }
        }

        // Fallback: split by whitespace if JSON decoding is not found or fails
        $normalizedText = strtolower($text);
        $cleanText = preg_replace('/[^\w\s]/u', ' ', $normalizedText);
        $words = preg_split('/\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

        return [
            'object_type' => null,
            'gender' => null,
            'color' => null,
            'keywords' => array_values(array_unique($words))
        ];
    }

    /**
     * Resolve mixed image input to an AI attachment.
     */
    protected function resolveImageAttachment(mixed $image): mixed
    {
        if ($image instanceof UploadedFile) {
            if (!$image->isValid()) {
                throw new \Exception('Uploaded file is invalid: ' . $image->getErrorMessage());
            }
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
