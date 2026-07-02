<?php

namespace SGCart\CrmTickets\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sgcart:crm-tickets-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the CrmTickets package resources (such as publishing the HasTickets trait stub)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing SGCart CRM Tickets package resources...');

        $traitsDir = app_path('Traits');
        if (!File::exists($traitsDir)) {
            File::makeDirectory($traitsDir, 0755, true);
        }

        $traitPath = $traitsDir . '/HasTickets.php';

        $stub = <<<'PHP'
<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTickets
{
    /**
     * Get all tickets linked to this model (polymorphic).
     */
    public function tickets(): MorphMany
    {
        if (class_exists(\SGCart\CrmTickets\Models\Ticket::class)) {
            return $this->morphMany(\SGCart\CrmTickets\Models\Ticket::class, 'ticketable');
        }

        // Fallback: Return a dummy MorphMany relationship pointing to self
        // but constrained to return an empty collection so that it is safe
        // even if the package is removed from the codebase.
        return $this->morphMany(self::class, 'ticketable')->whereRaw('1 = 0');
    }
}
PHP;

        File::put($traitPath, $stub);
        $this->info('Published App\Traits\HasTickets successfully!');

        // Publish package config
        $this->callSilent('vendor:publish', [
            '--tag' => 'crm-tickets-config',
        ]);
        $this->info('Published package configuration successfully!');
    }
}
