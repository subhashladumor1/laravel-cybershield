<?php

namespace CyberShield\Logging;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class LogContextBuilder
{
    /**
     * Enrich the log data with contextual information.
     *
     * @param array $data
     * @return array
     */
    public function build(array $data): array
    {
        $context = [
            'timestamp' => now()->toDateTimeString(),
            'ip' => Request::ip() ?? '127.0.0.1',
            'method' => Request::method() ?? 'N/A',
            'url' => Request::fullUrl() ?? 'N/A',
            'status' => $data['status'] ?? 'N/A',
            'user_id' => Auth::id() ?? 'Guest',
            'user_agent' => Request::userAgent() ?? 'N/A',
            'message' => $data['message'] ?? '',
        ];

        return array_merge($context, $data);
    }
}
