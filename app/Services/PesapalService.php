<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\PendingPayment;
use Illuminate\Support\Str;

class PesapalService
{
    protected $config;

    public function __construct()
    {
        $settings = \App\Models\Setting::singleton();
        $this->config = $settings->payload['pesapal'] ?? [];
    }

    public function enabled(): bool
    {
        return !empty($this->config['consumer_key']) && !empty($this->config['consumer_secret']);
    }

    /**
     * Create a pending payment with an external checkout URL when possible.
     * Returns array with keys: success (bool), redirect_url (string|null), pending (PendingPayment)
     */
    public function createPayment(PendingPayment $pending): array
    {
        // If not configured, return without creating external checkout
        if (!$this->enabled()) {
            return ['success' => false, 'redirect_url' => null, 'pending' => $pending];
        }

        // Example: API 3.0 uses OAuth2 / API key flows. We'll attempt a POST to the checkout endpoint.
        // The exact implementation depends on Pesapal API 3.0 contract; this is a conservative attempt
        // that may require adjustments for field names, signing, or OAuth token exchange.

        $sandbox = $this->config['sandbox'] ?? true;
        $base = $sandbox ? 'https://demo.pesapal.com' : 'https://www.pesapal.com';
        $endpoint = $base . '/api/3/checkout';

        // Build the callback URL dynamically based on environment
        $callbackUrl = config('app.url') . '/api/payments/pesapal/callback';
        $ipnUrl = config('app.url') . '/api/payments/pesapal/ipn';

        $payload = [
            'amount' => number_format($pending->amount, 2, '.', ''),
            'currency' => $this->config['currency'] ?? 'KES',
            'reference' => $pending->reference ?? \Illuminate\Support\Str::uuid()->toString(),
            'invoice_id' => $pending->invoice_id,
            'callback_url' => $this->config['callback_url'] ?? $callbackUrl,
            'notification_id' => $ipnUrl,
        ];

        try {
            $resp = Http::withBasicAuth($this->config['consumer_key'], $this->config['consumer_secret'])
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($resp->ok()) {
                $body = $resp->json();
                $url = $body['checkout_url'] ?? ($body['data']['checkout_url'] ?? null);
                if ($url) {
                    // persist reference if not set
                    $pending->reference = $payload['reference'];
                    $pending->meta = array_merge($pending->meta ?? [], ['pesapal_response' => $body]);
                    $pending->save();
                    return ['success' => true, 'redirect_url' => $url, 'pending' => $pending];
                }
            }
        } catch (\Throwable $e) {
            // swallow and fallback to simulation
        }

        return ['success' => false, 'redirect_url' => null, 'pending' => $pending];
    }

    /**
     * Very small helper to verify a webhook signature. Expects header value and payload
     * Uses HMAC-SHA256 with the configured secret if present.
     */
    public function verifySignature(?string $signature, string $payload): bool
    {
        $secret = $this->config['webhook_secret'] ?? null;
        if (empty($secret) || empty($signature)) {
            return false;
        }
        $calc = hash_hmac('sha256', $payload, $secret);
        return hash_equals($calc, $signature);
    }
}
