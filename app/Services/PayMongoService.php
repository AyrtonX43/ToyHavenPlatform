<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected string $secretKey;
    protected string $publicKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key') ?? '';
        $this->publicKey = config('services.paymongo.public_key') ?? '';
        $this->baseUrl = config('services.paymongo.base_url') ?? 'https://api.paymongo.com/v1';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->secretKey) && ! empty($this->publicKey);
    }

    /**
     * Create a Payment Intent for a given amount in PHP.
     *
     * @param  float  $amount  Amount in PHP (e.g. 150.00)
     * @param  string  $currency
     * @param  array  $metadata  Arbitrary key-value metadata attached to the intent
     * @return array|null  The payment intent resource data, or null on failure
     */
    public function createPaymentIntent(float $amount, string $currency = 'PHP', array $metadata = [], array $paymentMethods = ['card']): ?array
    {
        if (! $this->isConfigured()) {
            Log::error('PayMongo: API keys not configured. Check PAYMONGO_SECRET_KEY and PAYMONGO_PUBLIC_KEY in .env and run php artisan config:clear');

            return null;
        }

        try {
            $amountCentavos = (int) round($amount * 100);
            if ($amountCentavos < 2000) {
                $amountCentavos = 2000; // PayMongo minimum ₱20
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountCentavos,
                            'payment_method_allowed' => $paymentMethods,
                            'payment_method_options' => [
                                'card' => ['request_three_d_secure' => 'any'],
                            ],
                            'currency' => $currency,
                            'capture_type' => 'automatic',
                            'metadata' => $metadata,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('PayMongo: Create Payment Intent failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo: Create Payment Intent exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Detect which payment methods are actually enabled on this PayMongo account
     * by creating a minimal test intent and reading back the allowed methods.
     */
    public function getAvailablePaymentMethods(): array
    {
        $allMethods = ['card', 'gcash', 'paymaya'];

        if (! $this->isConfigured()) {
            return ['card'];
        }

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => 2000,
                            'payment_method_allowed' => $allMethods,
                            'currency' => 'PHP',
                            'capture_type' => 'automatic',
                            'metadata' => ['probe' => 'true'],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('data.attributes.payment_method_allowed') ?? ['card'];
            }

            $errors = $response->json('errors') ?? [];
            $disallowed = [];
            foreach ($errors as $error) {
                $detail = $error['detail'] ?? '';
                foreach (['gcash', 'paymaya'] as $method) {
                    if (stripos($detail, $method) !== false && stripos($detail, 'not allowed') !== false) {
                        $disallowed[] = $method;
                    }
                }
            }

            if (! empty($disallowed)) {
                return array_values(array_diff($allMethods, $disallowed));
            }

            return ['card'];
        } catch (\Exception $e) {
            Log::warning('PayMongo: Could not detect available payment methods', ['message' => $e->getMessage()]);

            return ['card'];
        }
    }

    /**
     * Retrieve a Payment Intent by ID.
     */
    public function getPaymentIntent(string $paymentIntentId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->get("{$this->baseUrl}/payment_intents/{$paymentIntentId}");

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('PayMongo: Get Payment Intent failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo: Get Payment Intent exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Verify a webhook signature from PayMongo.
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        $webhookSecret = config('services.paymongo.webhook_secret');
        if (empty($webhookSecret)) {
            return true; // Skip verification if no secret configured
        }

        $parts = collect(explode(',', $signatureHeader))
            ->mapWithKeys(function ($part) {
                [$key, $value] = explode('=', $part, 2) + [null, null];

                return [$key => $value];
            });

        $timestamp = $parts->get('t');
        $testSignature = $parts->get('te');
        $liveSignature = $parts->get('li');

        $signature = $liveSignature ?: $testSignature;
        if (! $timestamp || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);

        return hash_equals($expected, $signature);
    }
}
