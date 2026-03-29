<?php

namespace CyberShield\Security\Project;

use CyberShield\Security\Project\Contracts\ScannerRuleInterface;
use CyberShield\Security\Project\Rules\MalwareRule;
use CyberShield\Security\Project\Rules\SqlInjectionRule;
use CyberShield\Security\Project\Rules\XssRule;
use CyberShield\Security\Project\Rules\ConfigRule;
use CyberShield\Security\Project\Rules\DependencyRule;

use CyberShield\Security\Project\Rules\ModelSecurityRule;
use CyberShield\Security\Project\Rules\FileUploadRule;
use CyberShield\Security\Project\Rules\BotDetectionRule;
use CyberShield\Security\Project\Rules\ApiSecurityRule;
use CyberShield\Security\Project\Rules\AuthSecurityRule;
use CyberShield\Security\Project\Rules\InfrastructureRule;

class ProjectScanner
{
    protected $rules = [];

    public function __construct()
    {
        $this->registerDefaultRules();
    }

    protected function registerDefaultRules()
    {
        $rules = (array) shield_config('project_scanner.rules', [
            MalwareRule::class,
            SqlInjectionRule::class,
            XssRule::class,
            ConfigRule::class,
            DependencyRule::class,
            ModelSecurityRule::class,
            FileUploadRule::class,
            BotDetectionRule::class,
            ApiSecurityRule::class,
            AuthSecurityRule::class,
            InfrastructureRule::class,
        ]);

        foreach ($rules as $ruleClass) {
            if (class_exists($ruleClass)) {
                $this->addRule(new $ruleClass());
            }
        }
    }

    public function addRule(ScannerRuleInterface $rule)
    {
        $this->rules[get_class($rule)] = $rule;
    }

    public function removeRule(string $ruleClass)
    {
        unset($this->rules[$ruleClass]);
    }

    public function getRule(string $ruleClass): ?ScannerRuleInterface
    {
        return $this->rules[$ruleClass] ?? null;
    }

    public function scan(): array
    {
        $results = [];

        foreach ($this->rules as $rule) {
            $results[$rule->getName()] = $rule->scan();
        }

        return $results;
    }

    // Proxy methods for backward compatibility and command integration
    public function scanMalware() { return $this->runRule(MalwareRule::class); }
    public function scanSqlInjection() { return $this->runRule(SqlInjectionRule::class); }
    public function scanXss() { return $this->runRule(XssRule::class); }
    public function scanConfig() { return $this->runRule(ConfigRule::class); }
    public function scanDependencies() { return $this->runRule(DependencyRule::class); }
    public function scanModels() { return $this->runRule(ModelSecurityRule::class); }
    public function scanFileUploads() { return $this->runRule(FileUploadRule::class); }
    public function scanBots() { return $this->runRule(BotDetectionRule::class); }
    public function scanApi() { return $this->runRule(ApiSecurityRule::class); }
    public function scanAuth() { return $this->runRule(AuthSecurityRule::class); }
    public function scanInfrastructure() { return $this->runRule(InfrastructureRule::class); }

    protected function runRule(string $ruleClass)
    {
        $rule = $this->getRule($ruleClass);
        return $rule ? $rule->scan() : [];
    }
}

