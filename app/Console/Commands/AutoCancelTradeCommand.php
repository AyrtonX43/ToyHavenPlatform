<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Services\TradeMeetupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelTradeCommand extends Command
{
    protected $signature = 'trades:auto-cancel';

    protected $description = 'Auto-cancel trades where one party requested cancellation and the other did not respond within 24 hours';

    public function handle(): int
    {
        $meetupService = app(TradeMeetupService::class);

        $trades = Trade::whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) {
                $cutoff = now()->subHours(24);
                $q->where(function ($q2) use ($cutoff) {
                    $q2->whereNotNull('initiator_cancel_requested_at')
                        ->whereNull('participant_cancel_requested_at')
                        ->where('initiator_cancel_requested_at', '<=', $cutoff);
                })->orWhere(function ($q2) use ($cutoff) {
                    $q2->whereNull('initiator_cancel_requested_at')
                        ->whereNotNull('participant_cancel_requested_at')
                        ->where('participant_cancel_requested_at', '<=', $cutoff);
                });
            })
            ->get();

        $count = 0;
        foreach ($trades as $trade) {
            try {
                DB::transaction(function () use ($trade, $meetupService) {
                    $conversation = $trade->conversation;
                    $meetupService->cancel($trade);
                    if ($conversation) {
                        $conversation->messages()->create([
                            'sender_id' => null,
                            'is_system' => true,
                            'system_type' => 'cancelled',
                            'message' => 'Trade auto-cancelled: The other party did not respond to the cancellation request within 24 hours. Listings have been returned to active.',
                        ]);
                        $conversation->update(['last_message_at' => now(), 'is_locked' => true]);
                    }
                });
                $count++;
            } catch (\Throwable $e) {
                Log::warning('Auto-cancel trade failed: ' . $e->getMessage(), ['trade_id' => $trade->id]);
            }
        }

        $this->info("Auto-cancelled {$count} trade(s).");

        return 0;
    }
}
