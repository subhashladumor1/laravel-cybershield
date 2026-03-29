<?php

namespace CyberShield\Security\Api;

use Illuminate\Http\Request;

class ApiGateway
{
    public function __construct(
        protected readonly ApiRequestValidator $validator,
        protected readonly ApiRateLimiter $rateLimiter,
        protected readonly BehaviorAnalyzer $behaviorAnalyzer,
        protected readonly ThreatResponseEngine $threatEngine
    ) {
    }

    /**
     * Process the API request through all 4 security layers.
     */
    public function process(Request $request): void
    {
        // Layer 1: Request Validation (Keys, Signatures, Nonces)
        $this->validator->validate($request);

        // Layer 2: Rate Limiting & Throttling
        $this->rateLimiter->check($request);

        // Layer 3: Behavior Analysis & Fingerprinting
        $this->behaviorAnalyzer->analyze($request);

        // Layer 4: Threat Response & Action
        $this->threatEngine->handle($request);
    }

    /**
     * Release resources after request.
     */
    public function terminate(Request $request)
    {
        $this->rateLimiter->releaseConcurrent($request);
    }
}

