<?php

namespace CyberShield\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

abstract class BaseSecurityCommand extends Command
{
    protected $packageName = 'CyberShield';
    protected $version = '1.0.0';

    /**
     * Display the CyberShield Header
     */
    protected function displayHeader()
    {
        $colors = ['blue', 'cyan', 'magenta', 'red', 'yellow', 'green'];
        $randomColor = $colors[array_rand($colors)];

        $this->output->getFormatter()->setStyle('title', new OutputFormatterStyle($randomColor, null, ['bold']));
        $this->output->getFormatter()->setStyle('package', new OutputFormatterStyle('white', $randomColor, ['bold']));

        $this->line("");
        $this->line(" <package> {$this->packageName} </package> <title>v{$this->version} - Security Suite</title>");
        $this->line(" <fg=gray>====================================================</>");
        $this->line("");
    }

    /**
     * Run a scan step with a progress indicator
     */
    protected function runScanStep($label, $callback)
    {
        $this->output->write(" <fg=cyan>➜</> {$label} " . str_repeat('.', 30 - strlen($label)) . " ");

        $result = $callback();

        if ($result === true || (is_array($result) && count($result) === 0)) {
            $this->line("<fg=green;options=bold>DONE</>");
            return true;
        } else {
            $count = is_array($result) ? count($result) : 1;
            $this->line("<fg=yellow;options=bold>{$count} ISSUE(S)</>");
            return $result;
        }
    }

    /**
     * Print a summary of findings
     */
    protected function printSummary(array $findings)
    {
        $this->line("");
        $this->line(" <fg=white;bg=blue;options=bold> SCAN SUMMARY </>");
        $this->line(" <fg=gray>----------------------------------------------------</>");

        $totalIssues = 0;
        foreach ($findings as $category => $issues) {
            if (is_array($issues)) {
                $count = count($issues);
                $totalIssues += $count;
                if ($count > 0) {
                    $this->line(" <fg=yellow>⚠ {$category}:</> <fg=white>{$count} potential risks found.</>");
                    foreach ($issues as $issue) {
                        $this->line("   <fg=gray>└</> <fg=red>{$issue}</>");
                    }
                } else {
                    $this->line(" <fg=green>✔ {$category}:</> <fg=gray>No issues found.</>");
                }
            }
        }

        $this->line(" <fg=gray>----------------------------------------------------</>");
        if ($totalIssues > 0) {
            $this->line(" <fg=white;bg=red;options=bold> TOTAL ISSUES: {$totalIssues} </>");
            $this->warn("\n Security risk detected! Please review the findings above.");
        } else {
            $this->line(" <fg=white;bg=green;options=bold> SYSTEM SECURE </>");
            $this->info("\n All scans passed. Your Laravel application looks secure!");
        }
        $this->line("");
    }
}

