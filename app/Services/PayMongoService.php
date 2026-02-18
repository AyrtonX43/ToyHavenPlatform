<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected $secretKey;
    protected $publicKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
        $this->baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent($amount, $currency = 'PHP', $metadata = [])
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100, // Convert to centavos
                            'currency' => $currency,
                            'metadata' => $metadata,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::error('PayMongo Payment Intent Error', [
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a payment link
     */
    public function createPaymentLink($amount, $description, $referenceNumber, $currency = 'PHP')
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/links", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100, // Convert to centavos
                            'currency' => $currency,
                            'description' => $description,
                            'remarks' => "Order: {$referenceNumber}",
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::error('PayMongo Payment Link Error', [
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Retrieve payment intent
     */
    public function getPaymentIntent($id)
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/payment_intents/{$id}");

            if ($response->successful()) {
                return $response->json()['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Payment Intent Error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook($payload, $signature)
    {
        // Implement webhook signature verification
        // This is important for security
        return true; // Placeholder
    }
}
