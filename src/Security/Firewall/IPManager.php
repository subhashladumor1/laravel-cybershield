<?php

namespace CyberShield\Security\Firewall;

class IPManager
{
    /**
     * Check if an IP address is whitelisted.
     *
     * @param string $ip
     * @return bool
     */
    public function isWhitelisted(string $ip): bool
    {
        $whitelist = shield_config('whitelist', []);
        return $this->checkIpInList($ip, $whitelist);
    }

    /**
     * Check if an IP address is blacklisted.
     *
     * @param string $ip
     * @return bool
     */
    public function isBlacklisted(string $ip): bool
    {
        $blacklist = shield_config('blacklist', []);
        return $this->checkIpInList($ip, $blacklist);
    }

    /**
     * Check if an IP is in a given list (supports CIDR).
     *
     * @param string $ip
     * @param array $list
     * @return bool
     */
    protected function checkIpInList(string $ip, array $list): bool
    {
        foreach ($list as $range) {
            if ($this->ipInNetwork($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if an IP is within a CIDR range or matches exactly.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    protected function ipInNetwork(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($network, $mask) = explode('/', $range);
        
        $ip_long = ip2long($ip);
        $network_long = ip2long($network);
        $mask_dec = ~((1 << (32 - $mask)) - 1);
        
        return ($ip_long & $mask_dec) === ($network_long & $mask_dec);
    }
}

