<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\ConversationReport;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TradeItem;
use App\Models\TradeListing;
use App\Models\TradeListingImage;
use App\Models\TradeOffer;
use App\Models\Trade;
use App\Models\UserProduct;
use App\Models\UserProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetHostingerData extends Command
{
    protected $signature = 'data:reset-hostinger 
                            {--products : Delete toyshop products}
                            {--trades : Delete trade lists and user products}
                            {--conversations : Delete all chat conversations}
                            {--notifications : Delete all notifications for all users}
                            {--all : Delete products, trades, conversations, and notifications (default)}
                            {--force : Skip confirmation}
                            {--skip-storage : Do not delete files from storage}';

    protected $description = 'Delete toyshop products, trade lists, and chat conversations from the Hostinger database';

    public function handle(): int
    {
        $doProducts = $this->option('products') || $this->option('all');
        $doTrades = $this->option('trades') || $this->option('all');
        $doConversations = $this->option('conversations') || $this->option('all');
        $doNotifications = $this->option('notifications') || $this->option('all');

        if (!$doProducts && !$doTrades && !$doConversations && !$doNotifications) {
            $doProducts = $doTrades = $doConversations = $doNotifications = true;
        }

        $actions = collect([
            $doProducts && 'toyshop products',
            $doTrades && 'trade lists and user products',
            $doConversations && 'all chat conversations',
            $doNotifications && 'all notifications (for all users)',
        ])->filter()->implode(', ');

        if (!$this->option('force') && !$this->confirm("This will permanently delete: {$actions}. Continue?")) {
            return 0;
        }

        $skipStorage = $this->option('skip-storage');

        try {
            DB::beginTransaction();

            if ($doConversations) {
                $this->deleteConversations($skipStorage);
            }

            if ($doTrades) {
                $this->deleteTradeData($skipStorage);
            }

            if ($doProducts) {
                $this->deleteProducts($skipStorage);
            }

            if ($doNotifications) {
                $this->deleteAllNotifications();
            }

            DB::commit();
            $this->info('Data reset completed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function deleteConversations(bool $skipStorage): void
    {
        $this->info('Deleting chat conversations...');

        // Delete message attachments and their files
        $attachments = MessageAttachment::all();
        $count = $attachments->count();
        foreach ($attachments as $a) {
            if (!$skipStorage && $a->file_path && Storage::disk('public')->exists($a->file_path)) {
                Storage::disk('public')->delete($a->file_path);
            }
        }
        MessageAttachment::query()->delete();

        Message::query()->delete();
        ConversationReport::query()->delete();
        Conversation::query()->delete();

        DB::table('conversations')->update(['last_message_at' => null]);

        $this->line("  Deleted {$count} attachments, all messages and conversations.");
    }

    protected function deleteTradeData(bool $skipStorage): void
    {
        $this->info('Deleting trade lists and user products...');

        // 1. Delete trade items (must be before trades)
        $tradeItemsCount = TradeItem::query()->count();
        TradeItem::query()->delete();
        $this->line("  Deleted {$tradeItemsCount} trade items.");

        // 2. Delete trades
        $tradesCount = Trade::query()->count();
        Trade::query()->delete();
        $this->line("  Deleted {$tradesCount} trades.");

        // 3. Delete trade offers
        $offersCount = TradeOffer::query()->count();
        TradeOffer::query()->delete();
        $this->line("  Deleted {$offersCount} trade offers.");

        // 4. Delete trade listing images and their files
        $tliImages = TradeListingImage::with('tradeListing')->get();
        foreach ($tliImages as $img) {
            if (!$skipStorage && $img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }
        $tliCount = TradeListingImage::query()->count();
        TradeListingImage::query()->delete();
        $this->line("  Deleted {$tliCount} trade listing images.");

        // 5. Delete trade listing image_path files (single image column)
        $listings = TradeListing::whereNotNull('image_path')->get();
        foreach ($listings as $l) {
            if (!$skipStorage && $l->image_path && Storage::disk('public')->exists($l->image_path)) {
                Storage::disk('public')->delete($l->image_path);
            }
        }

        // 6. Delete trade listings
        $listingsCount = TradeListing::query()->count();
        TradeListing::query()->delete();
        $this->line("  Deleted {$listingsCount} trade listings.");

        // 7. Delete user product images and their files
        $upiImages = UserProductImage::all();
        foreach ($upiImages as $img) {
            if (!$skipStorage && $img->image_path && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }
        $upiCount = UserProductImage::query()->count();
        UserProductImage::query()->delete();
        $this->line("  Deleted {$upiCount} user product images.");

        // 8. Delete user products
        $userProductsCount = UserProduct::query()->count();
        UserProduct::query()->delete();
        $this->line("  Deleted {$userProductsCount} user products.");
    }

    protected function deleteProducts(bool $skipStorage): void
    {
        $this->info('Deleting toyshop products...');

        // Products with order_items cannot be deleted (FK restrict)
        $productIdsWithOrders = DB::table('order_items')->distinct()->pluck('product_id');
        $deletableProducts = Product::query()
            ->whereNotIn('id', $productIdsWithOrders)
            ->get();

        $skipped = Product::query()->whereIn('id', $productIdsWithOrders)->count();
        if ($skipped > 0) {
            $this->warn("  Skipping {$skipped} product(s) that appear in orders (to preserve order history).");
        }

        foreach ($deletableProducts as $product) {
            // Delete product image files
            if (!$skipStorage) {
                foreach ($product->images as $img) {
                    if ($img->image_path && Storage::disk('public')->exists($img->image_path)) {
                        Storage::disk('public')->delete($img->image_path);
                    }
                    if (!empty($img->hd_url)) {
                        $hdPath = parse_url($img->hd_url, PHP_URL_PATH);
                        if ($hdPath && str_starts_with($hdPath, '/storage/')) {
                            $relPath = substr($hdPath, strlen('/storage/'));
                            if (Storage::disk('public')->exists($relPath)) {
                                Storage::disk('public')->delete($relPath);
                            }
                        }
                    }
                }
                // Delete product videos
                if ($product->video_url) {
                    $path = parse_url($product->video_url, PHP_URL_PATH);
                    if ($path && str_starts_with($path, '/storage/')) {
                        $relPath = substr($path, strlen('/storage/'));
                        if (Storage::disk('public')->exists($relPath)) {
                            Storage::disk('public')->delete($relPath);
                        }
                    }
                }
            }

            $product->forceDelete();
        }

        $count = $deletableProducts->count();
        $this->line("  Deleted {$count} products.");
    }

    protected function deleteAllNotifications(): void
    {
        $this->info('Deleting all notifications for all users...');
        $count = DB::table('notifications')->count();
        DB::table('notifications')->truncate();
        $this->line("  Deleted {$count} notifications.");
    }
}
