<?php

namespace App\Console\Commands;

use App\Models\Seller;
use App\Notifications\SellerApplicationSubmittedNotification;
use Illuminate\Console\Command;

class SyncPendingSellerNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seller:sync-pending-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send application submitted notifications to existing pending sellers who did not receive them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync pending seller notifications...');
        
        // Get all pending sellers
        $pendingSellers = Seller::with('user')
            ->where('verification_status', 'pending')
            ->get();
        
        if ($pendingSellers->isEmpty()) {
            $this->info('No pending sellers found.');
            return 0;
        }
        
        $this->info('Found ' . $pendingSellers->count() . ' pending seller(s).');
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($pendingSellers as $seller) {
            if (!$seller->user) {
                $this->warn('Seller ID ' . $seller->id . ' has no associated user. Skipping...');
                $failCount++;
                continue;
            }
            
            // Check if notification already exists
            $existingNotification = $seller->user->notifications()
                ->where('type', 'App\Notifications\SellerApplicationSubmittedNotification')
                ->where('data->business_name', $seller->business_name)
                ->exists();
            
            if ($existingNotification) {
                $this->info('Notification already exists for ' . $seller->business_name . ' (User: ' . $seller->user->name . '). Skipping...');
                continue;
            }
            
            try {
                $shopType = $seller->is_verified_shop ? 'Verified Trusted Toyshop' : 'Local Business Toyshop';
                
                // Send notification
                $seller->user->notify(new SellerApplicationSubmittedNotification($shopType, $seller->business_name));
                
                $this->info('✓ Sent notification to ' . $seller->user->name . ' for business: ' . $seller->business_name);
                $successCount++;
                
            } catch (\Exception $e) {
                $this->error('✗ Failed to send notification to ' . $seller->user->name . ': ' . $e->getMessage());
                $failCount++;
            }
        }
        
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info('Total pending sellers: ' . $pendingSellers->count());
        $this->info('Successfully sent: ' . $successCount);
        $this->info('Failed: ' . $failCount);
        $this->info('Skipped (already notified): ' . ($pendingSellers->count() - $successCount - $failCount));
        
        $this->newLine();
        $this->info('Sync completed!');
        
        return 0;
    }
}
