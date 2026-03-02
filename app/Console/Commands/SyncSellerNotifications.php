<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Seller;
use App\Notifications\SellerRejectedNotification;
use App\Notifications\SellerSuspendedNotification;

class SyncSellerNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sellers:sync-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retroactively create in-app notifications for all rejected and suspended sellers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync seller notifications...');
        
        $rejectedCount = 0;
        $suspendedCount = 0;
        
        // Process rejected sellers
        $this->info('Processing rejected sellers...');
        $rejectedSellers = Seller::with('user')
            ->where('verification_status', 'rejected')
            ->whereNotNull('rejection_reason')
            ->get();
        
        foreach ($rejectedSellers as $seller) {
            if ($seller->user) {
                // Check if notification already exists
                $existingNotification = $seller->user->notifications()
                    ->where('type', SellerRejectedNotification::class)
                    ->where('data->business_name', $seller->business_name)
                    ->first();
                
                if (!$existingNotification) {
                    $seller->user->notify(new SellerRejectedNotification(
                        $seller->rejection_reason,
                        $seller->business_name
                    ));
                    $rejectedCount++;
                    $this->line("✓ Created notification for rejected seller: {$seller->business_name}");
                }
            }
        }
        
        // Process suspended sellers
        $this->info('Processing suspended sellers...');
        $suspendedSellers = Seller::with('user')
            ->where('is_active', false)
            ->whereNotNull('suspension_reason')
            ->get();
        
        foreach ($suspendedSellers as $seller) {
            if ($seller->user) {
                // Check if notification already exists
                $existingNotification = $seller->user->notifications()
                    ->where('type', SellerSuspendedNotification::class)
                    ->where('data->business_name', $seller->business_name)
                    ->first();
                
                if (!$existingNotification) {
                    $seller->user->notify(new SellerSuspendedNotification(
                        $seller->suspension_reason,
                        $seller->related_report_id,
                        $seller->business_name
                    ));
                    $suspendedCount++;
                    $this->line("✓ Created notification for suspended seller: {$seller->business_name}");
                }
            }
        }
        
        $this->newLine();
        $this->info("Sync completed successfully!");
        $this->info("Rejected seller notifications created: {$rejectedCount}");
        $this->info("Suspended seller notifications created: {$suspendedCount}");
        $this->info("Total notifications created: " . ($rejectedCount + $suspendedCount));
        
        return Command::SUCCESS;
    }
}
