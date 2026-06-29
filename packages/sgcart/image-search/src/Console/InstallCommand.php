<?php

namespace SGCart\ImageSearch\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image-search:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the ImageSearch package resources (such as the local HasSearchTerms trait stub)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->components->info('Installing ImageSearch package...');

        $traitsDir = app_path('Traits');
        if (!File::exists($traitsDir)) {
            File::makeDirectory($traitsDir, 0755, true);
        }

        $traitPath = $traitsDir . '/HasSearchTerms.php';

        $stub = <<<'PHP'
<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSearchTerms
{
    /**
     * Get all of the model's search terms.
     */
    public function searchTerms(): MorphMany
    {
        if (class_exists(\SGCart\ImageSearch\Models\SearchTerm::class)) {
            return $this->morphMany(\SGCart\ImageSearch\Models\SearchTerm::class, 'searchable');
        }

        // Fallback: Return a dummy MorphMany relationship pointing to self
        // but constrained to return an empty collection so that it is safe
        // even if the package is removed from the codebase.
        return $this->morphMany(self::class, 'searchable')->whereRaw('1 = 0');
    }
}
PHP;

        File::put($traitPath, $stub);
        $this->components->info('Published App\Traits\HasSearchTerms successfully!');

        // Publish package config
        $this->callSilent('vendor:publish', [
            '--tag' => 'image-search-config',
        ]);
        $this->components->info('Published package configuration successfully!');

        // Publish package migrations
        $this->callSilent('vendor:publish', [
            '--tag' => 'image-search-migrations',
        ]);
        $this->components->info('Published database migrations successfully!');
    }
}
