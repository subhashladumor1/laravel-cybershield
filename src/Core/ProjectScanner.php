<?php

namespace CyberShield\Core;

use Illuminate\Support\Facades\File;

class ProjectScanner
{
    /**
     * Scan files for malicious patterns
     */
    public function scanMalware()
    {
        $patterns = [
            'eval\s*\(' => 'Dangerous usage of eval() detected',
            'base64_decode\s*\(' => 'Potential obfuscated code using base64_decode()',
            'shell_exec\s*\(' => 'Execution of shell commands via shell_exec()',
            'system\s*\(' => 'Execution of system commands via system()',
            'exec\s*\(' => 'Execution of commands via exec()',
            'passthru\s*\(' => 'Execution of commands via passthru()',
            'popen\s*\(' => 'Execution of commands via popen()',
            '(?<![a-zA-Z0-9_])\$_GET\[' => 'Direct usage of $_GET superglobal',
            '(?<![a-zA-Z0-9_])\$_POST\[' => 'Direct usage of $_POST superglobal',
            '(?<![a-zA-Z0-9_])\$_REQUEST\[' => 'Direct usage of $_REQUEST superglobal',
        ];

        return $this->scanFiles($patterns, ['app', 'config', 'routes', 'public']);
    }

    /**
     * Scan for SQL injection risks
     */
    public function scanSqlInjection()
    {
        $patterns = [
            'DB::(select|raw|statement)\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'Raw SQL query with variable interpolation',
            '->whereRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'whereRaw() with variable interpolation',
            '->selectRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'selectRaw() with variable interpolation',
            '->orderByRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'orderByRaw() with variable interpolation',
            '->havingRaw\s*\(\s*["\'].*?\$[a-zA-Z0-9_]+.*?["\']' => 'havingRaw() with variable interpolation',
        ];

        return $this->scanFiles($patterns, ['app']);
    }

    /**
     * Scan for XSS vulnerabilities
     */
    public function scanXss()
    {
        $patterns = [
            '\{!!\s*\$[a-zA-Z0-9_]+\s*!!\}' => 'Unescaped Blade output {!! ... !!} detected',
            'echo\s+\$[a-zA-Z0-9_]+' => 'Native PHP echo without escaping',
            'print\s+\$[a-zA-Z0-9_]+' => 'Native PHP print without escaping',
        ];

        return $this->scanFiles($patterns, ['resources/views', 'app']);
    }

    /**
     * Scan models for mass assignment risks
     */
    public function scanModels()
    {
        $patterns = [
            'protected\s+\$guarded\s*=\s*\[\s*\]' => 'Model allows all fields to be mass-assigned ($guarded = [])',
            'protected\s+\$fillable\s*=\s*\[\s*["\']\*["\']\s*\]' => 'Model allows all fields to be mass-assigned ($fillable = ["*"])',
        ];

        return $this->scanFiles($patterns, ['app/Models']);
    }

    /**
     * Scan for insecure file uploads
     */
    public function scanFileUploads()
    {
        $patterns = [
            '->move\s*\(' => 'Direct move() of uploaded file without explicit validation check',
            '->storeAs\s*\(\s*.*?\.[a-z0-9]+["\']' => 'Fixed extension in storeAs() might be bypassable',
        ];

        return $this->scanFiles($patterns, ['app/Http/Controllers']);
    }

    /**
     * Check environment configuration
     */
    public function scanConfig()
    {
        $findings = [];

        if (config('app.debug') === true && app()->environment('production')) {
            $findings[] = 'APP_DEBUG is enabled in production environment';
        }

        if (config('app.key') === null || config('app.key') === '') {
            $findings[] = 'APP_KEY is not set';
        }

        if (config('session.driver') === 'cookie' && config('session.encrypt') === false) {
            $findings[] = 'Session driver is set to cookie without encryption';
        }

        return $findings;
    }

    /**
     * Scan for bot-related patterns in logs or code
     */
    public function scanBots()
    {
        $patterns = [
            'User-Agent' => 'Manual User-Agent handling detected',
            'curl_exec' => 'Outgoing request found, possible scraping or bot behavior',
            'GuzzleHttp' => 'Guzzle client usage, check for bot protection bypass',
        ];

        return $this->scanFiles($patterns, ['app']);
    }

    /**
     * Scan for API security risks
     */
    public function scanApi()
    {
        $patterns = [
            'Resource::collection' => 'API Resource collection usage, check for data exposure',
            'protected\s+\$hidden\s*=\s*\[\s*\]' => 'Potential sensitive data exposure in API model',
            'api_token' => 'Usage of api_token, check for secure hashing',
        ];

        return $this->scanFiles($patterns, ['app/Http/Resources', 'app/Models']);
    }

    /**
     * Scan for authentication risks
     */
    public function scanAuth()
    {
        $patterns = [
            'Auth::loginUsingId' => 'Dangerous usage of loginUsingId()',
            'password_hash' => 'Manual password hashing, use Laravel Hash instead',
            'AttemptLogin' => 'Custom login logic, check for rate limiting',
        ];

        return $this->scanFiles($patterns, ['app/Http/Controllers/Auth']);
    }

    /**
     * Scan for environment and config leaks
     */
    public function scanEnvLeaks()
    {
        $patterns = [
            'getenv\s*\(' => 'Usage of getenv() instead of config()',
            '\$_ENV\[' => 'Direct usage of $_ENV superglobal',
        ];

        return $this->scanFiles($patterns, ['app', 'config']);
    }

    /**
     * Scan dependencies for risks (basic)
     */
    public function scanDependencies()
    {
        $findings = [];
        $composerPath = base_path('composer.json');

        if (File::exists($composerPath)) {
            $content = File::get($composerPath);
            if (str_contains($content, '"*": "*"')) {
                $findings[] = 'Wildcard dependency versions detected in composer.json';
            }
        }

        return $findings;
    }

    /**
     * Scan infrastructure settings (basic)
     */
    public function scanInfrastructure()
    {
        $findings = [];

        // Check for common open ports or risky services in config
        if (config('database.connections.mysql.port') === '3306') {
            // This is just an example, usually we'd check server config if possible
        }

        $patterns = [
            'allow_url_fopen' => 'Potentially dangerous php.ini setting: allow_url_fopen',
            'display_errors' => 'Insecure php.ini setting: display_errors',
        ];

        return array_merge($findings, $this->scanFiles($patterns, ['config']));
    }

    /**
     * Generate a summary for reporting commands
     */
    public function scanReporting()
    {
        return ['Security audit reports are available in storage/logs/cybershield/reports'];
    }

    /**
     * Helper to scan files in directories for patterns
     */
    protected function scanFiles(array $patterns, array $directories)
    {
        $findings = [];
        foreach ($directories as $dir) {
            $path = base_path($dir);
            if (!File::isDirectory($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php' && $file->getExtension() !== 'blade.php') {
                    continue;
                }

                $content = File::get($file->getRealPath());
                foreach ($patterns as $pattern => $message) {
                    if (preg_match('/' . $pattern . '/i', $content)) {
                        $findings[] = $message . ' in ' . $file->getRelativePathname();
                    }
                }
            }
        }

        return $findings;
    }
}
