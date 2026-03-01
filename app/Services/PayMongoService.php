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
     * @return array|null  The full payment intent resource (id, attributes.client_key, etc.), or null on failure
     */
    public function createPaymentIntent(float $amount, string $currency = 'PHP', array $metadata = []): ?array
    {
        if (! $this->isConfigured()) {
            Log::error('PayMongo: API keys not configured.');

            return null;
        }

        try {
            $amountCentavos = (int) round($amount * 100);

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountCentavos,
                            'payment_method_allowed' => ['card', 'qrph'],
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
                $data = $response->json('data');
                Log::info('PayMongo: Payment intent created', [
                    'id' => $data['id'] ?? null,
                    'has_client_key' => ! empty($data['attributes']['client_key'] ?? null),
                ]);

                return $data;
            }

            Log::error('PayMongo: Create Payment Intent failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo: Create Payment Intent exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Attach a payment method to a payment intent (server-side with secret key).
     * Returns the full payment intent resource after attachment.
     */
    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId, ?string $returnUrl = null): ?array
    {
        try {
            $attrs = [
                'payment_method' => $paymentMethodId,
            ];

            if ($returnUrl) {
                $attrs['return_url'] = $returnUrl;
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_intents/{$paymentIntentId}/attach", [
                    'data' => [
                        'attributes' => $attrs,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json('data');
                Log::info('PayMongo: Attach payment method result', [
                    'payment_intent_id' => $paymentIntentId,
                    'status' => $data['attributes']['status'] ?? 'unknown',
                    'next_action_json' => json_encode($data['attributes']['next_action'] ?? null),
                    'return_url_sent' => $returnUrl,
                ]);

                return $data;
            }

            Log::error('PayMongo: Attach payment method failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo: Attach payment method exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Create a Card payment method server-side using the secret key.
     */
    public function createCardPaymentMethod(string $cardNumber, int $expMonth, int $expYear, string $cvc, string $name = '', string $email = ''): array
    {
        try {
            $cleanCard = preg_replace('/\D/', '', $cardNumber);
            $cleanCvc = preg_replace('/\D/', '', $cvc);

            Log::info('PayMongo: Creating card payment method', [
                'card_last4' => substr($cleanCard, -4),
                'card_length' => strlen($cleanCard),
                'exp_month' => $expMonth,
                'exp_year' => $expYear,
                'cvc_length' => strlen($cleanCvc),
                'using_key_prefix' => substr($this->secretKey, 0, 7),
            ]);

            $billing = [];
            if ($name) $billing['name'] = $name;
            if ($email) $billing['email'] = $email;

            $payload = [
                'data' => [
                    'attributes' => [
                        'type' => 'card',
                        'details' => [
                            'card_number' => $cleanCard,
                            'exp_month' => (int) $expMonth,
                            'exp_year' => (int) $expYear,
                            'cvc' => $cleanCvc,
                        ],
                    ],
                ],
            ];

            if (!empty($billing)) {
                $payload['data']['attributes']['billing'] = $billing;
            }

            Log::info('PayMongo: Card PM payload (sanitized)', [
                'card_length' => strlen($cleanCard),
                'card_first6' => substr($cleanCard, 0, 6),
                'card_last4' => substr($cleanCard, -4),
                'exp_month' => (int) $expMonth,
                'exp_year' => (int) $expYear,
                'cvc_length' => strlen($cleanCvc),
            ]);

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_methods", $payload);

            if ($response->successful()) {
                $pmId = $response->json('data.id');
                Log::info('PayMongo: Card payment method created', ['id' => $pmId]);
                return ['success' => true, 'id' => $pmId];
            }

            $errorBody = $response->json();
            $errorDetail = $errorBody['errors'][0]['detail'] ?? 'Unknown error from PayMongo';
            
            Log::error('PayMongo: Create card payment method failed', [
                'status' => $response->status(),
                'response' => $errorBody,
            ]);

            return ['success' => false, 'error' => $errorDetail];
        } catch (\Exception $e) {
            Log::error('PayMongo: Create card payment method exception', [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a QRPh payment method server-side.
     * Note: PayMongo requires public key for payment method creation.
     */
    public function createQrphPaymentMethod(string $name, string $email): ?string
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, '')
                ->withOptions(['verify' => config('app.env') !== 'local'])
                ->post("{$this->baseUrl}/payment_methods", [
                    'data' => [
                        'attributes' => [
                            'type' => 'qrph',
                            'billing' => [
                                'name' => $name,
                                'email' => $email,
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $pmId = $response->json('data.id');
                Log::info('PayMongo: QRPh payment method created', ['id' => $pmId]);
                return $pmId;
            }

            Log::error('PayMongo: Create QRPh payment method failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo: Create QRPh payment method exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Retrieve a Payment Intent by ID (using secret key for full access).
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
            return true;
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
