<?php

namespace CyberShield\BotDetection;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;

class BotDetector
{
    protected $bots = [
        'googlebot',
        'bingbot',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'yandexbot',
        'curl',
        'python',
        'postman',
        'selenium',
        'headless'
    ];

    public function analyze(Request $request)
    {
        $userAgent = strtolower($request->userAgent());

        // 1. Check known bot strings
        foreach ($this->bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                $this->handleBotDetected("Known bot detected: {$bot}", $request);
            }
        }

        // 2. Check for headless browser markers
        if ($this->isHeadless($request)) {
            $this->handleBotDetected("Headless browser detected", $request);
        }

        // 3. Check for header anomalies
        if ($this->hasHeaderAnomalies($request)) {
            $this->handleBotDetected("Header anomalies detected", $request);
        }
    }

    protected function isHeadless(Request $request)
    {
        // Check for specific headers or lack thereof
        return $request->header('X-Puppeteer-Request') ||
            ($request->header('Sec-Fetch-Mode') === 'navigate' && !$request->header('Accept-Language'));
    }

    protected function hasHeaderAnomalies(Request $request)
    {
        // Generic bots often miss common browser headers
        $commonHeaders = ['Accept', 'Accept-Encoding', 'Accept-Language', 'User-Agent'];
        foreach ($commonHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return true;
            }
        }
        return false;
    }

    protected function handleBotDetected($reason, Request $request)
    {
        if (config('cybershield.firewall.mode') === 'active') {
            throw new SecurityException($reason, 403);
        }
    }
}
