# Image Search Package for SGCart

This package provides AI-powered product suggestions and search via image upload. It integrates with Laravel AI to analyze uploaded images, identify products, extract keywords, colors, and gender, and match them against the SGCart catalog.

---

## Installation

To install the package in your SGCart application, follow these steps:

Run the package installation command using the DDEV wrapper:
```bash
composer require 'sgcart/image-search:@dev'
```
This command will:
- Copy the `HasSearchTerms` trait stub to `app/Traits/HasSearchTerms.php`.
- Publish the package configuration to `config/image-search.php`.
- Publish the database migrations to the main `database/migrations` directory.

### 3. Run Database Migrations
Run the migrations to create the `search_terms` and `image_search_settings` tables:
```bash
ddev artisan migrate
```

### 4. Integrate the Model Trait
Add the `HasSearchTerms` trait to your `App\Models\Product` model to enable the search terms relationship:
```php
namespace App\Models;

use App\Traits\HasSearchTerms;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasSearchTerms;
    
    // ...
}
```

### 5. Seed Search Terms (Optional)
To populate search terms for your existing mock products, you can run the seeder:
```bash
php artisan db:seed --class=SearchTermSeeder
```

---

## Configuration via Admin Panel

Once installed, a new **Configuration** section is added to the admin panel sidebar with a **Search** child option. 

Using this settings in Admin, you can configure:
- **AI Provider**: The text identifier of the AI driver (e.g., `gemini`, `openai`, `anthropic`).
- **Image Model**: The specific AI model identifier to use (e.g., `gemini-2.5-flash`, `gpt-4o`).
- **API Key**: The API key credential for the selected provider (includes a show/hide toggle).
- **Active / Inactive Toggle**: Instantly activate or deactivate the image search feature. When deactivated, the storefront camera icon and modal are hidden.

### Default Options & Environment Variables
If no settings are stored in the database yet (e.g., on initial installation), the package falls back to using the values from your `.env` file:
- **AI Provider**: `IMAGE_ANALYZER_PROVIDER` (defaults to `gemini`)
- **Image Model**: `IMAGE_ANALYZER_MODEL` (defaults to `gemini-2.5-flash`)
- **API Key**: Dynamic resolution based on the provider, falling back to `GEMINI_API_KEY` or `OPENAI_API_KEY`.

*Note: Saving settings via the admin panel writes the configuration to the database, which will always take priority and override the `.env` values at runtime.*

---

## Usage

### Storefront Search
1. When active, a camera icon appears inside the main search bar on the storefront.
2. Clicking the icon opens a file chooser. Selecting an image will immediately open the crop modal.
3. The selection box is initialized to cover the full image with a 12px margin, keeping the 8 drag handles fully visible.
4. Users can:
   - Drag any of the 8 handles (4 corners, 4 sides) to resize the selection area.
   - Drag inside the selection box to pan/move it.
   - Drag outside the selection box to clear it and draw a new selection area.
5. Clicking **Search Product** crops the selected area and submits it.

### AI Suggestion Algorithm
The package analyzes the cropped image and extracts:
- `object_type`: Matched against product titles, categories, or search terms.
- `color`: Matched against product titles or search terms (mandatory term).
- `gender` (optional): If a person is present, filters results through matching categories.
- `keywords`: Matches additional terms (min match of 1, non-exclusive) to refine the results.

---

## Uninstalling

To remove the package resources from the application:

```bash
composer remove 'sgcart/image-search'
```
