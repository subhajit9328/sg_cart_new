<?php

namespace SGCart\ImageSearch;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Promptable;
use Illuminate\Http\UploadedFile;

class MultiObjectDetector implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are an image analysis assistant specialized in detecting if an image contains multiple distinct objects or products.';
    }

    /**
     * Detect if the image contains multiple distinct products.
     */
    public function detect(mixed $image, ?string $provider = null, ?string $model = null): bool
    {
        $attachment = $this->resolveImageAttachment($image);

        $prompt = 'Analyze the image and determine if it contains multiple distinct objects or products.
Return a JSON object containing a single key "multiple" with a boolean value (true or false).
- Return true if there are multiple separate distinct objects, products, or items that can be searched for individually.
- Return false if there is only a single main product or object, or if it is a simple image with one clear subject.
Do NOT include any conversational text, markdown formatting, or code blocks.
Example output:
{"multiple": true}';

        $provider = $provider ?: config('image-search.image_analyzer_provider') ?: config('ai.default');
        $model = $model ?: config('image-search.image_analyzer_model');

        $response = $this->prompt($prompt, [$attachment], $provider, $model);
        $text = trim($response->text);

        // Parse JSON response
        if (preg_match('/\{[^\}]*\}/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (is_array($json) && isset($json['multiple'])) {
                return (bool) $json['multiple'];
            }
        }

        // Fallback: search for words "true" or "false" in the text
        if (str_contains(strtolower($text), 'true')) {
            return true;
        }

        return false;
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
