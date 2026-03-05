<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\Trade;
use App\Models\TradeItem;
use App\Models\TradeOffer;
use App\Models\TradeListing;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;

class TradeOfferService
{
    public function create(int $listingId, array $data): TradeOffer
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
            $offer = TradeOffer::with(['tradeListing', 'offeredTradeListing.images'])->findOrFail($offerId);

            if ($offer->status !== 'pending') {
                throw new \Exception('Offer cannot be accepted.');
            }

            if (!$offer->tradeListing->canAcceptOffers()) {
                throw new \Exception('Listing is not accepting offers.');
            }

            $trade = Trade::create([
                'trade_listing_id' => $offer->trade_listing_id,
                'trade_offer_id' => $offer->id,
                'initiator_id' => $offer->tradeListing->user_id,
                'initiator_seller_id' => $offer->tradeListing->seller_id,
                'participant_id' => $offer->offerer_id,
                'participant_seller_id' => $offer->offerer_seller_id,
                'cash_amount' => $offer->cash_amount,
                'status' => 'pending_meetup',
            ]);

            $listingItem = $offer->tradeListing->getItem();
            if ($listingItem) {
                $listingItem->loadMissing('images');
                $this->createTradeItem($trade, $listingItem, 'initiator');
            } else {
                $this->createTradeItemFromListing($trade, $offer->tradeListing->load('images'), 'initiator');
            }

            $offeredItem = $offer->getOfferedItem();
            if ($offeredItem) {
                if ($offeredItem instanceof TradeListing) {
                    $this->createTradeItemFromListing($trade, $offeredItem->loadMissing('images'), 'participant');
                } else {
                    $offeredItem->loadMissing('images');
                    $this->createTradeItem($trade, $offeredItem, 'participant');
                }
            }

            $offer->update(['status' => 'accepted']);
            $offer->tradeListing->update(['status' => 'pending_deal']);

            TradeOffer::where('trade_listing_id', $offer->trade_listing_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            if ($offer->tradeListing->product_id) {
                Product::where('id', $offer->tradeListing->product_id)->update(['trade_status' => 'in_trade']);
            } elseif ($offer->tradeListing->user_product_id) {
                UserProduct::where('id', $offer->tradeListing->user_product_id)->update(['status' => 'in_trade']);
            }

            if ($offer->offered_product_id) {
                Product::where('id', $offer->offered_product_id)->update(['trade_status' => 'in_trade']);
            } elseif ($offer->offered_user_product_id) {
                UserProduct::where('id', $offer->offered_user_product_id)->update(['status' => 'in_trade']);
            } elseif ($offer->offered_trade_listing_id) {
                TradeListing::where('id', $offer->offered_trade_listing_id)->update(['status' => 'pending_deal']);
            }

            $u1 = min($trade->initiator_id, $trade->participant_id);
            $u2 = max($trade->initiator_id, $trade->participant_id);
            Conversation::create([
                'trade_id' => $trade->id,
                'trade_listing_id' => $offer->trade_listing_id,
                'user1_id' => $u1,
                'user2_id' => $u2,
                'subject' => 'Offer Trade Approval - Trade #' . $trade->id,
            ]);

            return $trade;
        });
    }

    protected function createTradeItem(Trade $trade, $item, string $side): TradeItem
    {
        $userId = $side === 'initiator' ? $trade->initiator_id : $trade->participant_id;
        $sellerId = $side === 'initiator' ? $trade->initiator_seller_id : $trade->participant_seller_id;

        $images = [];
        if ($item->relationLoaded('images') && $item->images->isNotEmpty()) {
            $images = $item->images->pluck('image_path')->take(5)->values()->toArray();
        }

        $productId = $item instanceof Product ? $item->id : null;
        $userProductId = $item instanceof UserProduct ? $item->id : null;

        return TradeItem::create([
            'trade_id' => $trade->id,
            'product_id' => $productId,
            'user_product_id' => $userProductId,
            'user_id' => $userId,
            'seller_id' => $sellerId,
            'product_name' => $item->name ?? $item->title ?? 'Item',
            'product_description' => $item->description ?? null,
            'product_images' => $images,
            'product_condition' => $item->condition ?? 'used',
            'estimated_value' => $item instanceof UserProduct ? $item->estimated_value : ($item->final_price ?? $item->price ?? null),
            'side' => $side,
        ]);
    }

    protected function createTradeItemFromListing(Trade $trade, TradeListing $listing, string $side): TradeItem
    {
        $userId = $side === 'initiator' ? $trade->initiator_id : $trade->participant_id;
        $sellerId = $side === 'initiator' ? $trade->initiator_seller_id : $trade->participant_seller_id;
        $images = $listing->images->pluck('image_path')->take(5)->values()->toArray();
        return TradeItem::create([
            'trade_id' => $trade->id,
            'product_id' => null,
            'user_product_id' => null,
            'user_id' => $userId,
            'seller_id' => $sellerId,
            'product_name' => $listing->title,
            'product_description' => $listing->description,
            'product_images' => $images,
            'product_condition' => $listing->condition ?? 'used',
            'estimated_value' => $listing->cash_amount,
            'side' => $side,
        ]);
    }
}
