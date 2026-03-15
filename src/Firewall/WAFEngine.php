<?php

namespace CyberShield\Firewall;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class WAFEngine
{
    protected $signatures = [];

    public function __construct()
    {
        $this->loadSignatures();
    }

    protected function loadSignatures()
    {
        $types = ['sql', 'xss', 'rce', 'traversal'];
        foreach ($types as $type) {
            $path = __DIR__ . "/../Signatures/{$type}.json";
            if (file_exists($path)) {
                $this->signatures[$type] = json_decode(file_get_contents($path), true);
            }
        }
    }

    public function inspect(Request $request)
    {
        $payload = array_merge(
            $request->all(),
            $request->headers->all(),
            ['uri' => $request->getRequestUri()]
        );

        foreach ($this->signatures as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if ($this->match($pattern['regex'], $payload)) {
                    $this->triggerAttackDetected($type, $pattern, $request);
                }
            }
        }
    }

    protected function match($regex, $payload)
    {
        $jsonPayload = json_encode($payload);
        return preg_match($regex, $jsonPayload);
    }

    protected function triggerAttackDetected($type, $pattern, Request $request)
    {
        // Log event, block IP, etc.
        throw new SecurityException("Potential {$type} attack detected: " . $pattern['name'], 403);
    }
}
