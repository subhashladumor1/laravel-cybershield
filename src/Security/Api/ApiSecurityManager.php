<?php

namespace CyberShield\Security\Api;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class ApiSecurityManager
{
    public function __construct(
        protected readonly ApiGateway $gateway
    ) {
    }

    /**
     * Perform full API security verification.
     * This orchestrates the 4 layers: Gateway -> Rate Limiter -> Behavior Analyzer -> Threat Engine.
     *
     * @param Request $request
     * @return void
     * @throws SecurityException
     */
    public function verify(Request $request): void
    {
        if (!shield_config('modules.api_security', true)) {
            return;
        }

        $this->gateway->process($request);
    }

    /**
     * Clean up resources after the request (e.g., release concurrent slots).
     *
     * @param Request $request
     * @return void
     */
    public function terminate(Request $request): void
    {
        $this->gateway->terminate($request);
    }
}

