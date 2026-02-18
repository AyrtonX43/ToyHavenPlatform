<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Trade;
use App\Models\TradeOffer;
use App\Models\TradeListing;
use App\Models\TradeItem;
use App\Models\Product;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TradeService
{
    public function createTradeListing(array $data): TradeListing
    {
        return DB::transaction(function () use ($data) {
            $listing = TradeListing::create($data);

            // Update product status
            if ($listing->product_id) {
                Product::where('id', $listing->product_id)
                    ->update(['trade_status' => 'in_trade']);
            } elseif ($listing->user_product_id) {
                UserProduct::where('id', $listing->user_product_id)
                    ->update(['status' => 'in_trade']);
            }

            return $listing;
        });
    }

    public function createTradeOffer(int $listingId, array $data): TradeOffer
    {
        return DB::transaction(function () use ($listingId, $data) {
            $offer = TradeOffer::create(array_merge($data, [
                'trade_listing_id' => $listingId,
                'status' => 'pending',
            ]));

            TradeListing::where('id', $listingId)->increment('offers_count');

            return $offer;
        });
    }

    public function acceptOffer(int $offerId): Trade
    {
        return DB::transaction(function () use ($offerId) {
            $offer = TradeOffer::with(['tradeListing'])->findOrFail($offerId);

            if ($offer->status !== 'pending') {
                throw new \Exception('Offer cannot be accepted.');
            }

            if (!$offer->tradeListing->canAcceptOffers()) {
                throw new \Exception('Listing is not accepting offers.');
            }

            // Create trade
            $trade = Trade::create([
                'trade_listing_id' => $offer->trade_listing_id,
                'trade_offer_id' => $offer->id,
                'initiator_id' => $offer->tradeListing->user_id,
                'initiator_seller_id' => $offer->tradeListing->seller_id,
                'participant_id' => $offer->offerer_id,
                'participant_seller_id' => $offer->offerer_seller_id,
                'cash_amount' => $offer->cash_amount,
                'status' => 'pending_shipping',
            ]);

            // Create trade items for listing side
            $listingItem = $offer->tradeListing->getItem();
            if ($listingItem) {
                if ($listingItem instanceof Product) {
                    $listingItem->load('images');
                } elseif ($listingItem instanceof UserProduct) {
                    $listingItem->load('images');
                }
                $this->createTradeItem($trade, $listingItem, 'initiator');
            }

            // Create trade items for offer side
            $offeredItem = $offer->getOfferedItem();
            if ($offeredItem) {
                if ($offeredItem instanceof Product) {
                    $offeredItem->load('images');
                } elseif ($offeredItem instanceof UserProduct) {
                    $offeredItem->load('images');
                }
                $this->createTradeItem($trade, $offeredItem, 'participant');
            }

            // Update offer status
            $offer->update(['status' => 'accepted']);

            // Update listing status
            $offer->tradeListing->update(['status' => 'pending_trade']);

            // Reject other pending offers
            TradeOffer::where('trade_listing_id', $offer->trade_listing_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            // Update product statuses
            if ($offer->tradeListing->product_id) {
                Product::where('id', $offer->tradeListing->product_id)
                    ->update(['trade_status' => 'in_trade']);
            } elseif ($offer->tradeListing->user_product_id) {
                UserProduct::where('id', $offer->tradeListing->user_product_id)
                    ->update(['status' => 'in_trade']);
            }

            if ($offer->offered_product_id) {
                Product::where('id', $offer->offered_product_id)
                    ->update(['trade_status' => 'in_trade']);
            } elseif ($offer->offered_user_product_id) {
                UserProduct::where('id', $offer->offered_user_product_id)
                    ->update(['status' => 'in_trade']);
            }

            // Create trade conversation for chat (user1_id, user2_id)
            $initiatorId = $trade->initiator_id;
            $participantId = $trade->participant_id;
            $u1 = min($initiatorId, $participantId);
            $u2 = max($initiatorId, $participantId);
            Conversation::create([
                'trade_id' => $trade->id,
                'user1_id' => $u1,
                'user2_id' => $u2,
                'subject' => 'Trade #' . $trade->id,
            ]);

            return $trade;
        });
    }

    protected function createTradeItem(Trade $trade, $item, string $side): TradeItem
    {
        $data = [
            'trade_id' => $trade->id,
            'user_id' => $side === 'initiator' ? $trade->initiator_id : $trade->participant_id,
            'seller_id' => $side === 'initiator' ? $trade->initiator_seller_id : $trade->participant_seller_id,
            'side' => $side,
            'product_condition' => $item->condition ?? 'used',
        ];

        if ($item instanceof Product) {
            $data['product_id'] = $item->id;
            $data['product_name'] = $item->name;
            $data['product_description'] = $item->description;
            $data['product_images'] = $item->images ? $item->images->map(fn($img) => $img->image_path)->toArray() : [];
            $data['estimated_value'] = $item->price;
        } elseif ($item instanceof UserProduct) {
            $data['user_product_id'] = $item->id;
            $data['product_name'] = $item->name;
            $data['product_description'] = $item->description;
            $data['product_images'] = $item->images ? $item->images->map(fn($img) => $img->image_path)->toArray() : [];
            $data['estimated_value'] = $item->estimated_value;
        }

        return TradeItem::create($data);
    }

    public function updateTradeStatus(int $tradeId, string $status): Trade
    {
        $trade = Trade::findOrFail($tradeId);

        // Validate status transition
        $validTransitions = [
            'pending_shipping' => ['shipped', 'cancelled'],
            'shipped' => ['received', 'disputed'],
            'received' => ['completed', 'disputed'],
        ];

        if (!isset($validTransitions[$trade->status]) || !in_array($status, $validTransitions[$trade->status])) {
            throw new \Exception("Invalid status transition from {$trade->status} to {$status}");
        }

        $trade->update(['status' => $status]);

        if ($status === 'completed') {
            $trade->update(['completed_at' => now()]);

            // Update product statuses
            foreach ($trade->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->update(['trade_status' => 'traded']);
                } elseif ($item->user_product_id) {
                    UserProduct::where('id', $item->user_product_id)
                        ->update(['status' => 'traded']);
                }
            }

            // Update listing
            $trade->tradeListing->update(['status' => 'completed']);
        }

        return $trade;
    }

    public function calculateTradeValue(array $items): float
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['estimated_value'] ?? 0;
        }
        return $total;
    }

    public function validateTradeCompletion(int $tradeId): bool
    {
        $trade = Trade::findOrFail($tradeId);

        return $trade->initiator_received_at !== null && 
               $trade->participant_received_at !== null &&
               $trade->status !== 'completed';
    }
}
