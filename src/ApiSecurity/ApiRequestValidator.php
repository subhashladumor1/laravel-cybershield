<?php

namespace CyberShield\ApiSecurity;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use CyberShield\Exceptions\SecurityException;

class ApiRequestValidator
{
    /**
     * Validate the API Request.
     *
     * @param Request $request
     * @return void
     */
    public function validate(Request $request): void
    {
        // 1. Validate API Key
        $this->validateApiKey($request);

        // 2. Validate Request Signature
        if (shield_config('api_security.verify_signature', true)) {
            $this->validateSignature($request);
        }

        // 3. Validate Nonce and Replay Attack
        if (shield_config('api_security.replay_protection', true)) {
            $this->validateNonce($request);
        }

        // 4. Validate Timestamp
        $this->validateTimestamp($request);
    }

    /**
     * Validate API Key.
     */
    protected function validateApiKey(Request $request): void
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->query('api_key');

        if (!$apiKey) {
            shield_abort(401, 'API Key is missing', 'MissingApiKey');
        }

        $keyRecord = DB::table(shield_config('api_security.keys_table', 'api_keys'))
            ->where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$keyRecord) {
            shield_abort(401, 'Invalid API Key', 'InvalidApiKey');
        }

        // Check for expiration if column exists
        if (isset($keyRecord->expires_at) && $keyRecord->expires_at < now()) {
            shield_abort(401, 'API Key has expired', 'ExpiredApiKey');
        }

        // Attach key metadata to request for later use
        $request->merge(['_api_key_record' => $keyRecord]);
    }

    /**
     * Validate Request Signature (HMAC).
     */
    protected function validateSignature(Request $request): void
    {
        $signature = $request->header('X-Signature');
        if (!$signature) {
            shield_abort(403, 'Request signature is missing', 'MissingSignature');
        }

        $apiKeyRecord = $request->get('_api_key_record');
        $secret = $apiKeyRecord->secret ?? config('app.key');

        $method = $request->method();
        $url = $request->fullUrl();
        $body = $request->getContent();
        $timestamp = (string) $request->header('X-Timestamp', '');

        $payload = "{$method}|{$url}|{$body}|{$timestamp}";
        $algo = (string) shield_config('api_security.signature_algo', 'sha256');

        $expectedSignature = hash_hmac($algo, $payload, (string) $secret);

        if (!hash_equals($expectedSignature, (string) $signature)) {
            shield_abort(403, 'Invalid request signature', 'InvalidSignature');
        }
    }

    /**
     * Validate Nonce to prevent Replay Attacks.
     */
    protected function validateNonce(Request $request): void
    {
        $nonce = $request->header('X-Nonce');
        if (!$nonce || !is_string($nonce)) {
            shield_abort(403, 'Nonce is missing', 'MissingNonce');
        }

        $cacheKey = "cybershield:nonce:{$nonce}";

        if (Cache::has($cacheKey)) {
            shield_abort(403, 'Replay attack detected (Nonce already used)', 'ReplayAttack');
        }

        // Store nonce in cache for a limited time (e.g., 10 minutes)
        Cache::put($cacheKey, true, 600);
    }

    /**
     * Validate Request Timestamp to ensure it's within tolerance.
     */
    protected function validateTimestamp(Request $request): void
    {
        $timestamp = $request->header('X-Timestamp');
        if (!$timestamp) {
            return; // Optional: could be required
        }

        $tolerance = (int) shield_config('api_security.timestamp_tolerance', 60); // seconds
        $currentTime = time();

        if (abs($currentTime - (int) $timestamp) > $tolerance) {
            shield_abort(403, 'Request timestamp is outside tolerance limits', 'TimestampExpired');
        }
    }
}
