<?php

namespace CyberShield\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use CyberShield\Monitoring\MonitoringService;
use Symfony\Component\HttpFoundation\Response;

class MonitoringMiddleware
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $responseTime = (microtime(true) - $startTime) * 1000; // ms

        if (config('cybershield.monitoring.db_logging', true)) {
            $this->monitoringService->logRequest([
                'ip' => $request->ip(),
                'url' => $request->path(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time' => $responseTime,
                'user_agent' => $request->userAgent(),
                'payload' => $this->getSanitizedPayload($request),
            ]);
        }

        \CyberShield\Logging\SecurityLogger::log('request', [
            'status' => $response->getStatusCode(),
            'message' => "Request processed: {$request->method()} {$request->path()}",
        ]);

        return $response;
    }

    /**
     * Get sanitized payload to avoid logging sensitive data.
     */
    protected function getSanitizedPayload(Request $request): array
    {
        $payload = $request->all();
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'credit_card'];

        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '********';
            }
        }

        return $payload;
    }
}

