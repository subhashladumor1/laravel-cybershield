<?php

namespace CyberShield\Security\Network;

use Illuminate\Http\Request;
use CyberShield\Exceptions\SecurityException;
use CyberShield\Models\BlockedIp;
use Illuminate\Support\Facades\Cache;

class NetworkGuard
{
    public function check(Request $request)
    {
        $ip = $request->ip();

        // 1. Check Whitelist (Bypass everything else if whitelisted)
        if ($this->isWhitelisted($ip)) {
            return;
        }

        // 2. Check IP Blacklist
        $this->checkBlacklist($ip);

        // 3. Check Geo-blocking
        $this->checkGeo($request);

        // 4. Check TOR (if enabled)
        if (config('cybershield.network_security.block_tor')) {
            $this->checkTor($ip);
        }
    }

    protected function isWhitelisted($ip)
    {
        $whitelist = config('cybershield.whitelist', []);
        return check_ip_range($ip, $whitelist);
    }

    protected function checkBlacklist($ip)
    {
        // Check config blacklist
        $configBlacklist = config('cybershield.blacklist', []);
        if (check_ip_range($ip, $configBlacklist)) {
            $message = shield_config('network_security.messages.blacklisted', 'Your IP address ({ip}) is blacklisted.');
            throw new SecurityException(str_replace('{ip}', $ip, $message), 403);
        }

        // Check database for blocked IPs
        $blocked = BlockedIp::where('ip', $ip)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($blocked) {
            $message = shield_config('network_security.messages.blocked', 'Your IP address ({ip}) has been blocked. Reason: {reason}');
            $reason = $blocked->reason ?: 'Security violation';
            $finalMessage = str_replace(['{ip}', '{reason}'], [$ip, $reason], $message);
            throw new SecurityException($finalMessage, 403);
        }
    }

    protected function checkGeo(Request $request)
    {
        $blockedCountries = config('cybershield.network_security.blocked_countries', []);
        if (empty($blockedCountries)) {
            return;
        }

        $ip = $request->ip();
        $countryCode = ip_country_code();

        if ($countryCode && $countryCode !== 'UNKNOWN' && in_array(strtoupper($countryCode), array_map('strtoupper', $blockedCountries))) {
            $message = shield_config('network_security.messages.geo_blocked', 'Access denied from your location ({country}).');
            throw new SecurityException(str_replace('{country}', $countryCode, $message), 403);
        }
    }

    protected function checkTor($ip)
    {
        $isTor = Cache::remember("cybershield:is_tor:{$ip}", 3600, function () use ($ip) {
            // Simplified TOR check - in reality, you'd fetch a list of exit nodes
            // or use a service like torproject.org's DNSBL
            return false; // Placeholder
        });

        if ($isTor) {
            $message = shield_config('network_security.messages.tor_blocked', 'Access via TOR network is not allowed.');
            throw new SecurityException($message, 403);
        }
    }
}

