<?php

namespace CyberShield\Exceptions;

use Exception;

class SecurityException extends Exception
{
    protected $statusCode;

    public function __construct($message = "Security Violation", $statusCode = 403)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Security Violation',
            'message' => $this->getMessage(),
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String()
        ], $this->statusCode);
    }
}
