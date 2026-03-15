<?php

namespace CyberShield\Commands;

use CyberShield\Core\ProjectScanner;

class SecurityScanCommand extends BaseSecurityCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan {type=full : The type of scan to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run CyberShield security scan';

    /**
     * Run aliases
     */
    protected $aliases = [
        'security:scan:full',
        'security:scan:project',
        'security:scan:deep',
        'security:scan:quick',
        'security:scan:production',
        'security:scan:ci'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayHeader();

        $type = $this->argument('type');
        $scanner = new ProjectScanner();
        $findings = [];

        $this->info("Starting {$type} security audit...\n");

        if ($type === 'full' || $type === 'malware' || $type === 'virus') {
            $findings['Malware'] = $this->runScanStep('Malware Detection', fn() => $scanner->scanMalware());
        }

        if ($type === 'full' || $type === 'sql') {
            $findings['SQL Injection'] = $this->runScanStep('SQL Injection Check', fn() => $scanner->scanSqlInjection());
        }

        if ($type === 'full' || $type === 'xss') {
            $findings['XSS Vulnerabilities'] = $this->runScanStep('XSS Protection Check', fn() => $scanner->scanXss());
        }

        if ($type === 'full' || $type === 'models') {
            $findings['Data Models'] = $this->runScanStep('Mass Assignment Check', fn() => $scanner->scanModels());
        }

        if ($type === 'full' || $type === 'uploads') {
            $findings['File Security'] = $this->runScanStep('Upload Security Check', fn() => $scanner->scanFileUploads());
        }

        if ($type === 'full' || $type === 'config' || $type === 'env') {
            $findings['Configuration'] = $this->runScanStep('Environment Audit', fn() => $scanner->scanConfig());
        }

        if ($type === 'full' || $type === 'bot') {
            $findings['Bot Security'] = $this->runScanStep('Bot Protection Check', fn() => $scanner->scanBots());
        }

        if ($type === 'full' || $type === 'api') {
            $findings['API Security'] = $this->runScanStep('API Vulnerability Scan', fn() => $scanner->scanApi());
        }

        if ($type === 'full' || $type === 'auth') {
            $findings['Authentication'] = $this->runScanStep('Auth Security Audit', fn() => $scanner->scanAuth());
        }

        if ($type === 'full' || $type === 'dependencies') {
            $findings['Dependencies'] = $this->runScanStep('Dependency Audit', fn() => $scanner->scanDependencies());
        }

        if ($type === 'full' || $type === 'infrastructure') {
            $findings['Infrastructure'] = $this->runScanStep('Infrastructure Check', fn() => $scanner->scanInfrastructure());
        }

        $this->printSummary($findings);

        return 0;
    }
}
