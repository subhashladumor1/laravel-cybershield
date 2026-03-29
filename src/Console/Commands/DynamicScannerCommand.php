<?php

namespace CyberShield\Console\Commands;

use CyberShield\Core\ProjectScanner;

class DynamicScannerCommand extends BaseSecurityCommand
{
    protected $type;

    public function __construct(string $signature, string $type, string $description)
    {
        parent::__construct();
        $this->signature = $signature;
        $this->type = $type;
        $this->description = $description;
    }

    public function handle()
    {
        $this->displayHeader();

        $scanner = new ProjectScanner();
        $findings = [];

        $this->info("Starting " . strtoupper($this->type) . " security audit...\n");

        switch ($this->type) {
            case 'malware':
                $findings['Malware'] = $this->runScanStep('Malware Detection', fn() => $scanner->scanMalware());
                break;
            case 'sql':
                $findings['SQL Injection'] = $this->runScanStep('SQL Injection Check', fn() => $scanner->scanSqlInjection());
                break;
            case 'xss':
                $findings['XSS'] = $this->runScanStep('XSS Protection Check', fn() => $scanner->scanXss());
                break;
            case 'bot':
                $findings['Bots'] = $this->runScanStep('Bot Detection', fn() => $scanner->scanBots());
                break;
            case 'api':
                $findings['API'] = $this->runScanStep('API Security Check', fn() => $scanner->scanApi());
                break;
            case 'auth':
                $findings['Auth'] = $this->runScanStep('Authentication Audit', fn() => $scanner->scanAuth());
                break;
            case 'env':
                $findings['Environment'] = $this->runScanStep('Environment Check', fn() => $scanner->scanEnvLeaks());
                break;
            case 'dependency':
                $findings['Dependencies'] = $this->runScanStep('Dependency Scan', fn() => $scanner->scanDependencies());
                break;
            case 'file':
                $findings['FileUpload'] = $this->runScanStep('Upload Security Check', fn() => $scanner->scanFileUploads());
                break;
            case 'model':
                $findings['Models'] = $this->runScanStep('Mass Assignment Check', fn() => $scanner->scanModels());
                break;
            case 'config':
                $findings['Configuration'] = $this->runScanStep('Configuration Audit', fn() => $scanner->scanConfig());
                break;
            case 'infrastructure':
                $findings['Infrastructure'] = $this->runScanStep('Infrastructure Check', fn() => $scanner->scanInfrastructure());
                break;
            case 'reporting':
                $findings['Reporting'] = $this->runScanStep('Reporting Status', fn() => $scanner->scanReporting());
                break;
            case 'full':
            default:
                $findings['Malware'] = $this->runScanStep('Malware Detection', fn() => $scanner->scanMalware());
                $findings['SQL Injection'] = $this->runScanStep('SQL Injection Check', fn() => $scanner->scanSqlInjection());
                $findings['XSS'] = $this->runScanStep('XSS Protection Check', fn() => $scanner->scanXss());
                $findings['Models'] = $this->runScanStep('Mass Assignment Check', fn() => $scanner->scanModels());
                $findings['FileUpload'] = $this->runScanStep('Upload Security Check', fn() => $scanner->scanFileUploads());
                $findings['Bots'] = $this->runScanStep('Bot Detection', fn() => $scanner->scanBots());
                $findings['API'] = $this->runScanStep('API Security Check', fn() => $scanner->scanApi());
                $findings['Auth'] = $this->runScanStep('Authentication Audit', fn() => $scanner->scanAuth());
                $findings['Environment'] = $this->runScanStep('Environment Check', fn() => $scanner->scanEnvLeaks());
                $findings['Configuration'] = $this->runScanStep('Configuration Audit', fn() => $scanner->scanConfig());
                $findings['Dependencies'] = $this->runScanStep('Dependency Scan', fn() => $scanner->scanDependencies());
                break;
        }

        $this->printSummary($findings);

        return 0;
    }
}

