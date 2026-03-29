<?php

namespace CyberShield\Security\Project\Contracts;

interface ScannerRuleInterface
{
    /**
     * Get the name of the rule.
     */
    public function getName(): string;

    /**
     * Get the description of the rule.
     */
    public function getDescription(): string;

    /**
     * Run the scan and return findings.
     *
     * @param string|null $basePath
     * @return array
     */
    public function scan(?string $basePath = null): array;
}

