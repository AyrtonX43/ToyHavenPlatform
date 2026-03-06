<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\SavedAuction;
use Illuminate\Console\Command;

class NotifySavedAuctionsLive extends Command
{
    protected $signature = 'auction:notify-saved-live';

    protected $description = 'Notify users who saved auctions that are now live';

    public function handle(): int
    {
        $justWentLive = Auction::where('status', 'live')
            ->where('start_at', '>=', now()->subMinutes(5))
            ->pluck('id');

        $saved = SavedAuction::whereIn('auction_id', $justWentLive)
            ->whereNull('notified_at')
            ->with(['user', 'auction'])
            ->get();

        foreach ($saved as $s) {
            try {
                $s->user->notify(new \App\Notifications\AuctionLiveNotification($s->auction));
                $s->update(['notified_at' => now()]);
            } catch (\Exception $e) {
                // Skip on error
            }
        }

        return self::SUCCESS;
    }
}
