<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    /**
     * Handle PayMongo webhook events for subscriptions
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        if (empty($payload['data']['attributes']['type'])) {
            return response()->json(['received' => true], 200);
        }

        $eventType = $payload['data']['attributes']['type'];

        // Verify webhook signature in production
        $signature = $request->header('Paymongo-Signature');
        if (config('app.env') === 'production' && $signature && ! $this->payMongo->verifyWebhook($request->getContent(), $signature)) {
            Log::warning('PayMongo webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        try {
            match ($eventType) {
                'subscription.activated' => $this->handleSubscriptionActivated($payload),
                'subscription.updated' => $this->handleSubscriptionUpdated($payload),
                'subscription.invoice.paid' => $this->handleInvoicePaid($payload),
                'subscription.invoice.payment_failed' => $this->handleInvoicePaymentFailed($payload),
                'subscription.past_due' => $this->handleSubscriptionPastDue($payload),
                'subscription.unpaid' => $this->handleSubscriptionUnpaid($payload),
                default => null,
            };
        } catch (\Exception $e) {
            Log::error('PayMongo webhook error', [
                'event' => $eventType,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['received' => true], 200);
    }

    protected function getSubscriptionData(array $payload): ?array
    {
        $data = $payload['data']['attributes']['data'] ?? $payload['data'] ?? null;
        if (! $data) {
            return null;
        }
        $attrs = $data['attributes'] ?? $data;

        return [
            'id' => $attrs['id'] ?? $data['id'] ?? null,
            'status' => $attrs['status'] ?? null,
            'cancelled_at' => $attrs['cancelled_at'] ?? null,
        ];
    }

    protected function handleSubscriptionActivated(array $payload): void
    {
        $data = $this->getSubscriptionData($payload);
        if (! $data || ! $data['id']) {
            return;
        }

        $sub = Subscription::where('paymongo_subscription_id', $data['id'])->first();
        if ($sub) {
            $sub->update([
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        }
    }

    protected function handleSubscriptionUpdated(array $payload): void
    {
        $data = $this->getSubscriptionData($payload);
        if (! $data || ! $data['id']) {
            return;
        }

        $sub = Subscription::where('paymongo_subscription_id', $data['id'])->first();
        if (! $sub) {
            return;
        }

        $status = $data['status'] ?? null;
        if (in_array($status, ['cancelled', 'incomplete_cancelled'])) {
            $sub->update([
                'status' => 'cancelled',
                'cancelled_at' => $data['cancelled_at'] ? \Carbon\Carbon::createFromTimestamp($data['cancelled_at']) : now(),
            ]);
        }
    }

    protected function handleInvoicePaid(array $payload): void
    {
        $data = $payload['data']['attributes']['data'] ?? $payload['data'] ?? null;
        if (! $data) {
            return;
        }
        $attrs = $data['attributes'] ?? $data;
        $resourceId = $attrs['resource_id'] ?? null;
        $amount = ($attrs['amount'] ?? 0) / 100;

        $sub = Subscription::where('paymongo_subscription_id', $resourceId)->first();
        if ($sub) {
            $sub->update([
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
            $sub->payments()->create([
                'amount' => $amount,
                'paymongo_payment_id' => $attrs['id'] ?? null,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    protected function handleInvoicePaymentFailed(array $payload): void
    {
        $data = $payload['data']['attributes']['data'] ?? $payload['data'] ?? null;
        if (! $data) {
            return;
        }
        $attrs = $data['attributes'] ?? $data;
        $resourceId = $attrs['resource_id'] ?? null;

        $sub = Subscription::where('paymongo_subscription_id', $resourceId)->first();
        if ($sub) {
            $sub->update(['status' => 'past_due']);
        }
    }

    protected function handleSubscriptionPastDue(array $payload): void
    {
        $data = $this->getSubscriptionData($payload);
        if (! $data || ! $data['id']) {
            return;
        }

        Subscription::where('paymongo_subscription_id', $data['id'])
            ->update(['status' => 'past_due']);
    }

    protected function handleSubscriptionUnpaid(array $payload): void
    {
        $data = $this->getSubscriptionData($payload);
        if (! $data || ! $data['id']) {
            return;
        }

        Subscription::where('paymongo_subscription_id', $data['id'])
            ->update(['status' => 'expired']);
    }
}
