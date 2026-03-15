<?php

namespace CyberShield\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ReflectionClass;

class ListMiddlewareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cybershield:list-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available CyberShield security middlewares and their aliases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->header();

        $middlewarePath = __DIR__ . '/../Middleware/';
        if (!is_dir($middlewarePath)) {
            $this->error("Middleware directory not found at: $middlewarePath");
            return 1;
        }

        $files = glob($middlewarePath . '*Middleware.php');
        $rows = [];

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            if ($filename === 'generate_middlewares')
                continue;

            $alias = 'cybershield.' . Str::snake(str_replace('Middleware', '', $filename));

            // Basic categorization based on name prefix
            $category = $this->guessCategory($filename);

            $rows[] = [
                $category,
                $filename,
                $alias
            ];
        }

        // Sort by category then name
        usort($rows, function ($a, $b) {
            if ($a[0] === $b[0])
                return strcmp($a[1], $b[1]);
            return strcmp($a[0], $b[0]);
        });

        $this->table(['Category', 'Class Name', 'Alias'], $rows);

        $this->info("\nTotal Middlewares: " . count($rows));
        $this->comment("Usage: Route::get('/', ...)->middleware('cybershield.alias_name');");
    }

    protected function header()
    {
        $this->line('<fg=cyan>
   ______      __               _____ __    _      __    __
  / ____/_  __/ /_  ___  _____/ ___// /_  (_)__  / /___/ /
 / /   / / / / __ \/ _ \/ ___/\__ \/ __ \/ / _ \/ / __  / 
/ /___/ /_/ / /_/ /  __/ /   ___/ / / / / /  __/ / /_/ /  
\____/\__, /_.___/\___/_/   /____/_/ /_/_/\___/_/\__,_/   
     /____/                                               </>');
        $this->line(' <fg=yellow>Security Middleware Registry</>');
        $this->line('');
    }

    protected function guessCategory($name)
    {
        if (str_starts_with($name, 'ValidateRequest'))
            return 'A. Request Security';
        if (str_contains($name, 'RateLimiter'))
            return 'B. Rate Limiting';
        if (str_starts_with($name, 'Detect') && str_contains($name, 'Bot'))
            return 'C. Bot Protection';
        if (str_contains($name, 'Ip') || str_contains($name, 'Geo') || str_contains($name, 'Tor') || str_contains($name, 'Vpn') || str_contains($name, 'Network'))
            return 'D. Network Security';
        if (str_starts_with($name, 'Enforce'))
            return 'E. Auth Security';
        if (str_contains($name, 'Api'))
            return 'F. API Security';
        if (str_starts_with($name, 'Detect'))
            return 'G. Threat Detection';
        if (str_starts_with($name, 'Log'))
            return 'H. Monitoring';

        return 'Other';
    }
}
