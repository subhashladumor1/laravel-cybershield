<?php

namespace CyberShield\Security\Firewall;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class WAFEngine
{
    protected $signatures = [];

    public function __construct(
        protected readonly \CyberShield\Security\Signatures\SignatureLoader $loader
    ) {
        $this->loadSignatures();
    }

    protected function loadSignatures()
    {
        $this->signatures = $this->loader->loadAll();
    }

    public function inspect(Request $request)
    {
        // Define request parts to inspect from configuration
        $targets = (array) shield_config('firewall.inspection_targets', ['query', 'body', 'headers', 'uri']);
        
        $inspectionTargets = [];
        if (in_array('query', $targets)) $inspectionTargets['query'] = $request->query();
        if (in_array('body', $targets)) $inspectionTargets['body'] = $request->post();
        if (in_array('headers', $targets)) $inspectionTargets['headers'] = $request->headers->all();
        if (in_array('uri', $targets)) $inspectionTargets['uri'] = $request->getRequestUri();

        foreach ($this->signatures as $signature) {
            foreach ($signature->getRegexPatterns() as $pattern) {
                foreach ($inspectionTargets as $targetName => $payload) {
                    if ($this->match($pattern, $payload)) {
                        $this->triggerAttackDetected($signature, $targetName, $request);
                    }
                }
            }
        }
    }

    protected function match($regex, $payload)
    {
        if (empty($regex)) return false;
        
        if (is_array($payload)) {
            foreach ($payload as $value) {
                if ($this->match($regex, $value)) {
                    return true;
                }
            }
            return false;
        }

        return is_string($payload) && preg_match($regex, $payload);
    }

    protected function triggerAttackDetected(\CyberShield\Security\Signatures\Signature $signature, string $target, Request $request)
    {
        $ip = $request->ip();
        $message = "Potential {$signature->name} attack detected in {$target}: {$signature->description}";

        // Log the attack with critical level
        \CyberShield\Logging\SecurityLogger::critical('waf', [
            'ip'           => $ip,
            'attack_id'    => $signature->id,
            'attack_name'  => $signature->name,
            'severity'     => $signature->severity,
            'impact_score' => $signature->impactScore,
            'target'       => $target,
            'url'          => $request->fullUrl(),
            'message'      => $message,
            'tags'         => $signature->tags
        ]);

        // Auto-block if configured with dynamic TTL
        if (shield_config('threat_detection.block_on_threat', true)) {
             $severity = $signature->severity;
             $ttlConfig = shield_config('firewall.blocking_ttl', [
                 'low' => 1,
                 'medium' => 3,
                 'high' => 7,
                 'critical' => 30
             ]);
             
             $days = $ttlConfig[$severity] ?? $ttlConfig['medium'] ?? 3;

             \CyberShield\Models\BlockedIp::firstOrCreate(
                ['ip' => $ip],
                [
                    'reason'     => "WAF detected {$signature->name} attack ({$signature->id})",
                    'severity'   => $severity,
                    'expires_at' => now()->addDays((int) $days)
                ]
            );
        }

        throw new SecurityException($message, 403);
    }
}

