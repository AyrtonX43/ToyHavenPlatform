<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        if ($signature && ! $this->payMongo->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayMongo webhook: signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $body = json_decode($payload, true);
        $eventType = data_get($body, 'data.attributes.type');
        $resourceData = data_get($body, 'data.attributes.data');

        Log::info('PayMongo webhook received', ['type' => $eventType]);

        return match ($eventType) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($resourceData),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($resourceData),
            default => response()->json(['status' => 'ignored']),
        };
    }

    protected function handlePaymentSucceeded(array $data)
    {
        $paymentIntentId = $data['id'] ?? null;
        if (! $paymentIntentId) {
            return response()->json(['status' => 'no_id'], 400);
        }

        $metadata = data_get($data, 'attributes.metadata', []);

        // Toyshop order payment
        if ($orderNumber = ($metadata['order_number'] ?? null)) {
            $order = Order::where('order_number', $orderNumber)->first();
            if ($order && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentIntentId,
                ]);

                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'payment_confirmed',
                    'description' => 'Payment confirmed via PayMongo.',
                    'updated_by' => $order->user_id,
                ]);

                $order->seller?->user?->notify(new \App\Notifications\OrderPaidNotification($order));

                Log::info('PayMongo webhook: order payment confirmed', ['order' => $orderNumber]);
            }
        }

        // Membership subscription payment
        if ($subscriptionId = ($metadata['subscription_id'] ?? null)) {
            $subscription = Subscription::find($subscriptionId);
            if ($subscription && $subscription->status !== 'active') {
                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => $subscription->plan?->interval === 'yearly'
                        ? now()->addYear()
                        : now()->addMonth(),
                ]);

                SubscriptionPayment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => data_get($data, 'attributes.amount', 0) / 100,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('PayMongo webhook: subscription payment confirmed', ['subscription' => $subscriptionId]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    protected function handlePaymentFailed(array $data)
    {
        $metadata = data_get($data, 'attributes.metadata', []);

        if ($orderNumber = ($metadata['order_number'] ?? null)) {
            Log::warning('PayMongo webhook: payment failed for order', ['order' => $orderNumber]);
        }

        if ($subscriptionId = ($metadata['subscription_id'] ?? null)) {
            Log::warning('PayMongo webhook: payment failed for subscription', ['subscription' => $subscriptionId]);
        }

        return response()->json(['status' => 'noted']);
    }
}
