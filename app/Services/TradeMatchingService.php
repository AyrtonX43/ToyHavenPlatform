<?php

namespace App\Services;

use App\Models\TradeListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TradeMatchingService
{
    /**
     * Get suggested listings that might match the given listing (for "you might also match" on listing show).
     * Matches when: other listing's item matches this listing's desired_items, or vice versa.
     */
    public function getSuggestedListingsForListing(int $listingId, int $limit = 8): Collection
    {
        $listing = TradeListing::with(['product.category', 'userProduct.category'])
            ->active()
            ->find($listingId);

        if (!$listing) {
            return new Collection([]);
        }

        $searchTerms = $this->extractSearchTermsFromListing($listing);
        if ($searchTerms->isEmpty()) {
            return $this->getFallbackSuggestedListings($listingId, $listing->user_id ?? 0, $limit);
        }

        $query = TradeListing::with(['user', 'product.images', 'product.category', 'userProduct.images', 'userProduct.category'])
            ->active()
            ->where('id', '!=', $listingId)
            ->where('user_id', '!=', $listing->user_id);

        $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $term = trim((string) $term);
                if (strlen($term) < 2) {
                    continue;
                }
                $pattern = '%' . $term . '%';
                $q->orWhere('title', 'like', $pattern)
                    ->orWhere('description', 'like', $pattern)
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', $pattern)->orWhere('description', 'like', $pattern))
                    ->orWhereHas('userProduct', fn ($pq) => $pq->where('name', 'like', $pattern)->orWhere('description', 'like', $pattern))
                    ->orWhereHas('product.category', fn ($pq) => $pq->where('name', 'like', $pattern))
                    ->orWhereHas('userProduct.category', fn ($pq) => $pq->where('name', 'like', $pattern));
            }
        });

        return $query->orderByDesc('offers_count')->limit($limit)->get();
    }

    /**
     * Get suggested listings for the current user on the trading index ("Suggested for you").
     * Uses the user's products and listings to match other listings' desired_items or titles.
     */
    public function getSuggestedListingsForUser(int $userId, int $limit = 8): Collection
    {
        $userListings = TradeListing::with(['product', 'userProduct'])
            ->where('user_id', $userId)
            ->active()
            ->get();

        $searchTerms = collect();
        foreach ($userListings as $listing) {
            $item = $listing->getItem();
            if ($item) {
                $searchTerms->push($item->name);
                if (!empty($item->category_id) && $item->relationLoaded('category')) {
                    $searchTerms->push($item->category->name ?? '');
                } elseif (isset($item->category) && is_object($item->category)) {
                    $searchTerms->push($item->category->name ?? '');
                }
            }
            if (is_array($listing->desired_items)) {
                $searchTerms = $searchTerms->merge($listing->desired_items);
            }
        }

        $userProductNames = \App\Models\UserProduct::where('user_id', $userId)
            ->where('status', 'available')
            ->pluck('name')
            ->filter()
            ->toArray();
        $searchTerms = $searchTerms->merge($userProductNames)->unique()->filter(fn ($t) => is_string($t) && strlen(trim($t)) >= 2);

        if ($searchTerms->isEmpty()) {
            return TradeListing::with(['user', 'product.images', 'userProduct.images'])
                ->active()
                ->where('user_id', '!=', $userId)
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        }

        $query = TradeListing::with(['user', 'product.images', 'userProduct.images'])
            ->active()
            ->where('user_id', '!=', $userId);

        $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms->take(10) as $term) {
                $term = trim((string) $term);
                if ($term === '') {
                    continue;
                }
                $pattern = '%' . $term . '%';
                $q->orWhere('title', 'like', $pattern)
                    ->orWhere('description', 'like', $pattern)
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', $pattern))
                    ->orWhereHas('userProduct', fn ($pq) => $pq->where('name', 'like', $pattern));
            }
        });

        return $query->orderByDesc('offers_count')->orderByDesc('created_at')->limit($limit)->get();
    }

    protected function extractSearchTermsFromListing(TradeListing $listing): SupportCollection
    {
        $terms = collect();

        if (is_array($listing->desired_items)) {
            foreach ($listing->desired_items as $item) {
                if (is_string($item)) {
                    $terms->push($item);
                } elseif (is_numeric($item)) {
                    $terms->push((string) $item);
                }
            }
        }

        $listingItem = $listing->getItem();
        if ($listingItem) {
            $terms->push($listingItem->name ?? '');
            if (isset($listingItem->category) && is_object($listingItem->category)) {
                $terms->push($listingItem->category->name ?? '');
            }
        }

        return $terms->map(fn ($t) => trim((string) $t))->filter(fn ($t) => $t !== '')->unique();
    }

    protected function getFallbackSuggestedListings(int $excludeId, int $excludeUserId, int $limit): Collection
    {
        return TradeListing::with(['user', 'product.images', 'userProduct.images'])
            ->active()
            ->where('id', '!=', $excludeId)
            ->where('user_id', '!=', $excludeUserId)
            ->orderByDesc('offers_count')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
