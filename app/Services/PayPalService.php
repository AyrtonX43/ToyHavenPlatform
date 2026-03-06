<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $sandbox = config('services.paypal.sandbox', true);
        $this->baseUrl = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    protected function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::asForm()->withBasicAuth($this->clientId, $this->clientSecret)
            ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('PayPal: Failed to get access token', ['response' => $response->body()]);

        return null;
    }

    /**
     * Create a PayPal order for the given amount.
     *
     * @return array|null ['id' => order_id, 'approval_url' => url] or null
     */
    public function createOrder(float $amount, string $currency, string $returnUrl, string $cancelUrl, array $metadata = []): ?array
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return null;
        }

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', ''),
                ],
                'custom_id' => json_encode($metadata),
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => config('app.name', 'ToyHaven'),
            ],
        ];

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", $payload);

        if ($response->successful()) {
            $data = $response->json();
            $links = collect($data['links'] ?? []);
            $approveLink = $links->firstWhere('rel', 'approve');

            return [
                'id' => $data['id'],
                'approval_url' => $approveLink['href'] ?? null,
                'status' => $data['status'] ?? null,
            ];
        }

        Log::error('PayPal: Create order failed', [
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return null;
    }

    /**
     * Capture a PayPal order after user approval.
     */
    public function captureOrder(string $orderId): ?array
    {
        $token = $this->getAccessToken();
        if (! $token) {
            return null;
        }

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
