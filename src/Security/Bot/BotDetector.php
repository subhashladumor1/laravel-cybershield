<?php

namespace CyberShield\Security\Bot;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;
use Illuminate\Support\Facades\Cache;

class BotDetector
{
    protected function getBots(): array
    {
        return shield_config('bot_protection.bots', [
            'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
            'curl', 'python', 'postman', 'selenium', 'headless', 'phantomjs', 'gherkin',
            'scrapy', 'wget', 'urllib', 'httpclient', 'php', 'perl', 'ruby'
        ]);
    }

    protected function getSuspiciousHeaders(): array
    {
        return shield_config('bot_protection.suspicious_headers', [
            'X-Puppeteer-Request',
            'X-Selenium-Driver',
            'X-Headless-Chrome'
        ]);
    }

    public function analyze(Request $request)
    {
        $userAgent = strtolower($request->userAgent());
        $ip = $request->ip();

        // 1. Check known bot strings
        foreach ($this->getBots() as $bot) {
            if (str_contains($userAgent, $bot)) {
                $this->handleBotDetected("Known bot detected: {$bot}", $request);
                return;
            }
        }

        // 2. Check for headless browser markers and suspicious headers
        if ($this->isHeadless($request) || $this->hasSuspiciousHeaders($request)) {
            $this->handleBotDetected("Headless or automated browser detected", $request);
            return;
        }

        // 3. Check for header anomalies (browser-like requests missing common headers)
        if ($this->hasHeaderAnomalies($request)) {
            $this->handleBotDetected("Header anomalies detected", $request);
            return;
        }

        // 4. Behavioral Analysis: Fast request pacing
        if ($this->hasSuspiciousPacing($ip)) {
            $this->handleBotDetected("Suspicious request pacing detected", $request);
            return;
        }
    }

    protected function isHeadless(Request $request)
    {
        return ($request->header('Sec-Fetch-Mode') === 'navigate' && !$request->header('Accept-Language')) ||
               ($request->header('User-Agent') && str_contains(strtolower($request->header('User-Agent')), 'headless'));
    }

    protected function hasSuspiciousHeaders(Request $request)
    {
        foreach ($this->getSuspiciousHeaders() as $header) {
            if ($request->hasHeader($header)) {
                return true;
            }
        }
        return false;
    }

    protected function hasHeaderAnomalies(Request $request)
    {
        // Real browsers usually include these
        $commonHeaders = shield_config('bot_protection.browser_common_headers', [
            'Accept', 'Accept-Encoding', 'Accept-Language'
        ]);

        if ($request->isMethod('GET')) {
            foreach ($commonHeaders as $header) {
                if (!$request->hasHeader($header)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function hasSuspiciousPacing(string $ip)
    {
        $key = "cybershield:bot_pacing:{$ip}";
        $requests = (int) Cache::get($key, 0);
        
        $limit = (int) shield_config('bot_protection.pacing_limit', 50);
        $window = (int) shield_config('bot_protection.pacing_window', 10);

        if ($requests >= $limit) {
            return true;
        }

        Cache::put($key, $requests + 1, $window);
        return false;
    }

    protected function handleBotDetected($reason, Request $request)
    {
        $code = (int) shield_config('bot_protection.block_response_code', 403);
        shield_abort($code, $reason, 'BotDetector');
    }
}

