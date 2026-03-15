<?php

namespace CyberShield;

use Illuminate\Support\ServiceProvider;
use CyberShield\Commands\SecurityScanCommand;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class CyberShieldServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/cybershield.php', 'cybershield');

        $this->app->singleton(\CyberShield\Core\SecurityKernel::class, function ($app) {
            return new \CyberShield\Core\SecurityKernel($app);
        });

        $this->app->singleton(\CyberShield\Firewall\WAFEngine::class, function ($app) {
            return new \CyberShield\Firewall\WAFEngine();
        });

        $this->app->singleton(\CyberShield\BotDetection\BotDetector::class, function ($app) {
            return new \CyberShield\BotDetection\BotDetector();
        });

        $this->app->singleton(\CyberShield\RateLimiting\AdvancedRateLimiter::class, function ($app) {
            return new \CyberShield\RateLimiting\AdvancedRateLimiter();
        });

        $this->app->singleton(\CyberShield\ApiSecurity\ApiRequestValidator::class, function ($app) {
            return new \CyberShield\ApiSecurity\ApiRequestValidator();
        });

        $this->app->singleton(\CyberShield\ApiSecurity\ApiRateLimiter::class, function ($app) {
            return new \CyberShield\ApiSecurity\ApiRateLimiter();
        });

        $this->app->singleton(\CyberShield\ApiSecurity\BehaviorAnalyzer::class, function ($app) {
            return new \CyberShield\ApiSecurity\BehaviorAnalyzer();
        });

        $this->app->singleton(\CyberShield\ApiSecurity\ThreatResponseEngine::class, function ($app) {
            return new \CyberShield\ApiSecurity\ThreatResponseEngine();
        });

        $this->app->singleton(\CyberShield\ApiSecurity\ApiGateway::class, function ($app) {
            return new \CyberShield\ApiSecurity\ApiGateway(
                $app->make(\CyberShield\ApiSecurity\ApiRequestValidator::class),
                $app->make(\CyberShield\ApiSecurity\ApiRateLimiter::class),
                $app->make(\CyberShield\ApiSecurity\BehaviorAnalyzer::class),
                $app->make(\CyberShield\ApiSecurity\ThreatResponseEngine::class)
            );
        });

        $this->app->singleton(\CyberShield\ApiSecurity\ApiSecurityManager::class, function ($app) {
            return new \CyberShield\ApiSecurity\ApiSecurityManager($app->make(\CyberShield\ApiSecurity\ApiGateway::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/cybershield.php' => config_path('cybershield.php'),
            ], 'cybershield-config');

            $this->publishes([
                __DIR__ . '/database/migrations/' => database_path('migrations'),
            ], 'cybershield-migrations');

            $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/cybershield'),
            ], 'cybershield-views');

            // Register commands
            $this->registerCommands();
        }

        // Register All Security Middlewares Dynamically
        $middlewarePath = __DIR__ . '/Middleware/';
        if (is_dir($middlewarePath)) {
            $files = glob($middlewarePath . '*Middleware.php');
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $className = 'CyberShield\\Middleware\\' . $filename;
                $alias = 'cybershield.' . Str::snake(str_replace('Middleware', '', $filename));
                $router->aliasMiddleware($alias, $className);
            }
        }

        // Register Blade Directives
        $this->registerBladeDirectives();

        // Load helpers
        $this->loadHelpers();
    }

    protected function registerBladeDirectives(): void
    {
        // Register all security directives via the dedicated class
        (new \CyberShield\Blade\SecurityDirectives())->register();
    }

    protected function loadHelpers(): void
    {
        $helpers = __DIR__ . '/Helpers/security_helpers.php';
        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    protected function registerCommands(): void
    {
        $this->commands([
            SecurityScanCommand::class,
            \CyberShield\Commands\ListMiddlewareCommand::class,
        ]);

        $dynamicCommands = $this->getSecurityCommands();
        $commandInstances = [];

        foreach ($dynamicCommands as $signature => $config) {
            $commandInstances[] = new Commands\DynamicScannerCommand(
                $signature,
                $config['type'],
                $config['description']
            );
        }

        $this->commands($commandInstances);
    }

    protected function getSecurityCommands(): array
    {
        $scanCommands = [];

        // 1. Master Security Commands (10)
        $masters = ['security:scan:project', 'security:scan:full', 'security:scan:quick', 'security:scan:deep', 'security:scan:production', 'security:scan:ci', 'security:scan:report', 'security:scan:export', 'security:scan:fix'];
        foreach ($masters as $m)
            $scanCommands[$m] = ['type' => 'full', 'description' => 'Complete security audit'];

        // 2. Malware & Backdoor Detection (15)
        $malware = ['security:scan:malware', 'security:scan:virus', 'security:scan:backdoor', 'security:scan:webshell', 'security:scan:trojan', 'security:scan:suspicious-files', 'security:scan:eval-usage', 'security:scan:base64-code', 'security:scan:obfuscated-code', 'security:scan:encoded-code', 'security:scan:php-injection', 'security:scan:file-integrity', 'security:scan:unauthorized-files', 'security:scan:dangerous-functions', 'security:scan:shell-execution'];
        foreach ($malware as $m)
            $scanCommands[$m] = ['type' => 'malware', 'description' => 'Scan for malware and backdoors'];

        // 3. SQL Injection (10)
        $sql = ['security:scan:sql', 'security:scan:sql-injection', 'security:scan:unsafe-query', 'security:scan:raw-sql', 'security:scan:query-builder-risk', 'security:scan:dynamic-sql', 'security:scan:database-leak', 'security:scan:db-permissions', 'security:scan:db-config', 'security:scan:query-patterns'];
        foreach ($sql as $s)
            $scanCommands[$s] = ['type' => 'sql', 'description' => 'Scan for SQL injection risks'];

        // 4. XSS & Input (10)
        $xss = ['security:scan:xss', 'security:scan:html-output', 'security:scan:unsafe-blade', 'security:scan:unescaped-output', 'security:scan:unsafe-js', 'security:scan:input-sanitization', 'security:scan:dangerous-html', 'security:scan:user-input', 'security:scan:script-injection', 'security:scan:dom-xss'];
        foreach ($xss as $x)
            $scanCommands[$x] = ['type' => 'xss', 'description' => 'Scan for XSS risks'];

        // 5. File & Upload (10)
        $files = ['security:scan:file-upload', 'security:scan:file-permissions', 'security:scan:dangerous-extensions', 'security:scan:storage-exposure', 'security:scan:public-files', 'security:scan:upload-validation', 'security:scan:executable-files', 'security:scan:archive-bomb', 'security:scan:file-signature', 'security:scan:storage-security'];
        foreach ($files as $f)
            $scanCommands[$f] = ['type' => 'file', 'description' => 'Scan for file upload security'];

        // 6. Bot & Traffic (10)
        $bots = ['security:scan:bot', 'security:scan:bot-traffic', 'security:scan:scraper', 'security:scan:automation', 'security:scan:fake-browser', 'security:scan:traffic-anomaly', 'security:scan:request-pattern', 'security:scan:api-abuse', 'security:scan:ddos-pattern', 'security:scan:bot-signature'];
        foreach ($bots as $b)
            $scanCommands[$b] = ['type' => 'bot', 'description' => 'Analyze bot and traffic patterns'];

        // 7. API Security (10)
        $api = ['security:scan:api', 'security:scan:api-auth', 'security:scan:api-token', 'security:scan:api-rate-limit', 'security:scan:api-endpoints', 'security:scan:api-exposure', 'security:scan:api-permissions', 'security:scan:api-security', 'security:scan:api-signature', 'security:scan:api-replay'];
        foreach ($api as $a)
            $scanCommands[$a] = ['type' => 'api', 'description' => 'Scan API endpoints for security'];

        // 8. Authentication (10)
        $auth = ['security:scan:auth', 'security:scan:password', 'security:scan:otp', 'security:scan:2fa', 'security:scan:login', 'security:scan:session', 'security:scan:token', 'security:scan:account-lock', 'security:scan:auth-policy', 'security:scan:auth-vulnerabilities'];
        foreach ($auth as $a)
            $scanCommands[$a] = ['type' => 'auth', 'description' => 'Audit authentication security'];

        // 9. Model & Database (10)
        $models = ['security:scan:models', 'security:scan:mass-assignment', 'security:scan:model-fillable', 'security:scan:model-guarded', 'security:scan:db-relations', 'security:scan:db-index', 'security:scan:db-constraints', 'security:scan:db-tables', 'security:scan:db-columns', 'security:scan:data-leak'];
        foreach ($models as $m)
            $scanCommands[$m] = ['type' => 'model', 'description' => 'Analyze model and database security'];

        // 10. Environment & Config (10)
        $config = ['security:scan:env', 'security:scan:debug', 'security:scan:keys', 'security:scan:secrets', 'security:scan:config', 'security:scan:filesystem', 'security:scan:queue', 'security:scan:cache', 'security:scan:session', 'security:scan:mail'];
        foreach ($config as $c)
            $scanCommands[$c] = ['type' => 'config', 'description' => 'Scan environment configuration'];

        // 11. Dependency Scan (10)
        $deps = ['security:scan:dependencies', 'security:scan:composer', 'security:scan:vulnerabilities', 'security:scan:outdated-packages', 'security:scan:security-advisories', 'security:scan:package-risk', 'security:scan:vendor-malware', 'security:scan:library-check', 'security:scan:package-integrity', 'security:scan:dependency-audit'];
        foreach ($deps as $d)
            $scanCommands[$d] = ['type' => 'dependency', 'description' => 'Audit composer dependencies'];

        // 12. Infrastructure Scan (10)
        $infra = ['security:scan:server', 'security:scan:php-config', 'security:scan:filesystem-permissions', 'security:scan:cron', 'security:scan:ports', 'security:scan:tls', 'security:scan:ssl', 'security:scan:server-headers', 'security:scan:security-headers', 'security:scan:firewall'];
        foreach ($infra as $i)
            $scanCommands[$i] = ['type' => 'infrastructure', 'description' => 'Check server and infrastructure security'];

        // 13. Reporting (10)
        $reports = ['security:report', 'security:report:json', 'security:report:html', 'security:report:pdf', 'security:report:dashboard', 'security:report:summary', 'security:report:threats', 'security:report:vulnerabilities', 'security:report:logs', 'security:report:audit'];
        foreach ($reports as $r)
            $scanCommands[$r] = ['type' => 'reporting', 'description' => 'Generate security reports'];

        return $scanCommands;
    }
}
