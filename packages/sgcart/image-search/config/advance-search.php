<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Analyzer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can configure the default AI provider and model that the
    | package will use for image-based product suggestions/search.
    |
    */

    'image_analyzer_provider' => env('IMAGE_ANALYZER_PROVIDER'),
    'image_analyzer_model' => env('IMAGE_ANALYZER_MODEL'),
];
