<?php

namespace App\Console\Commands;

use App\Models\Auction;
use Illuminate\Console\Command;

class StartLiveAuctions extends Command
{
    protected $signature = 'auction:start-live';

    protected $description = 'Set approved auctions to live when start_at is reached';

    public function handle(): int
    {
        $count = Auction::where('status', 'approved')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', now())
            ->update(['status' => 'live']);

        if ($count > 0) {
            $this->info("Started {$count} auction(s).");
        }

        return self::SUCCESS;
    }
}
