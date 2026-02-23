<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected $secretKey;

    protected $publicKey;

    protected $baseUrl;

    protected $baseUrlV2 = 'https://api.paymongo.com/v2';

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
        $this->baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Basic '.base64_encode($this->secretKey.':'),
        ];
    }

    /**
     * Create a payment intent for one-time payments (orders)
     */
    public function createPaymentIntent($amount, $currency = 'PHP', $metadata = [])
    {
        try {
            $amountCentavos = (int) round($amount * 100);
            if ($amountCentavos < 2000) {
                $amountCentavos = 2000; // PayMongo minimum
            }

            // Metadata values must be strings
            $metadataStrings = array_map(fn ($v) => (string) $v, $metadata);

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountCentavos,
                            'currency' => $currency,
                            'payment_method_allowed' => ['card', 'gcash', 'paymaya', 'qrph', 'grab_pay', 'shopee_pay'],
                            'metadata' => $metadataStrings,
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

    /**
     * Create a recurring plan for subscriptions
     *
     * @param  int  $amount  Amount in PHP (e.g. 99 for 99.00)
     * @param  string  $interval  weekly, monthly, or yearly
     * @param  string  $name  Plan name
     * @param  string  $description  Plan description
     * @param  int|null  $cycleCount  Billing cycles (null = indefinite)
     */
    public function createPlan(int $amount, string $interval, string $name, string $description = '', ?int $cycleCount = null, string $currency = 'PHP')
    {
        try {
            $amountCentavos = (int) round($amount * 100);
            if ($amountCentavos < 2000) {
                $amountCentavos = 2000; // PayMongo minimum
            }

            $payload = [
                'data' => [
                    'attributes' => [
                        'amount' => $amountCentavos,
                        'currency' => $currency,
                        'interval' => $interval,
                        'interval_count' => 1,
                        'name' => $name,
                        'description' => $description ?: $name,
                    ],
                ],
            ];

            if ($cycleCount !== null && $cycleCount >= 2) {
                $payload['data']['attributes']['cycle_count'] = $cycleCount;
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/subscriptions/plans", $payload);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::error('PayMongo Create Plan Error', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Create or retrieve PayMongo customer for a user
     */
    public function createCustomer(User $user): ?array
    {
        if ($user->paymongo_customer_id) {
            return $this->getCustomer($user->paymongo_customer_id);
        }

        try {
            $payload = [
                'name' => $user->name,
                'email' => $user->email,
            ];

            if ($user->phone) {
                $phone = preg_replace('/[^0-9+]/', '', $user->phone);
                if (! str_starts_with($phone, '+')) {
                    $phone = '+63'.$phone; // Philippines default
                }
                if (strlen($phone) >= 10) {
                    $payload['mobile_phone'] = $phone;
                }
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrlV2}/customers", $payload);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? $response->json();
                $customerId = $data['id'] ?? null;
                if ($customerId) {
                    $user->update(['paymongo_customer_id' => $customerId]);
                }

                return $data;
            }

            Log::error('PayMongo Create Customer Error', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get customer by ID
     */
    public function getCustomer(string $customerId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrlV2}/customers/{$customerId}");

            if ($response->successful()) {
                return $response->json()['data'] ?? $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Customer Error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Create a subscription linking customer to plan
     */
    public function createSubscription(string $customerId, string $planId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/subscriptions", [
                    'data' => [
                        'attributes' => [
                            'customer_id' => $customerId,
                            'plan_id' => $planId,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::error('PayMongo Create Subscription Error', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/subscriptions/{$subscriptionId}/cancel");

            if ($response->successful()) {
                return $response->json()['data'];
            }

            Log::error('PayMongo Cancel Subscription Error', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get subscription by ID
     */
    public function getSubscription(string $subscriptionId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/subscriptions/{$subscriptionId}");

            if ($response->successful()) {
                return $response->json()['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Subscription Error', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
