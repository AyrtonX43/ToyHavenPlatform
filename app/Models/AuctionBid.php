<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionBid extends Model
{
    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
        'rank_at_bid',
        'is_winning',
        'bidder_alias',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_winning' => 'boolean',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique anonymous alias for a bidder within an auction.
     * Each user gets ONE consistent alias per auction (re-uses on re-bid).
     */
    public static function resolveAlias(int $auctionId, int $userId): string
    {
        $existing = static::where('auction_id', $auctionId)
            ->where('user_id', $userId)
            ->whereNotNull('bidder_alias')
            ->value('bidder_alias');

        if ($existing) {
            return $existing;
        }

        return static::generateUniqueAlias($auctionId);
    }

    private static function generateUniqueAlias(int $auctionId): string
    {
        $adjectives = [
            'Swift', 'Bold', 'Lucky', 'Brave', 'Clever', 'Eager', 'Fierce',
            'Gentle', 'Happy', 'Jolly', 'Keen', 'Loyal', 'Mighty', 'Noble',
            'Proud', 'Quick', 'Royal', 'Sharp', 'Smart', 'Steady', 'Bright',
            'Calm', 'Daring', 'Elite', 'Grand', 'Iron', 'Jade', 'Mystic',
            'Pixel', 'Rapid', 'Silent', 'Titan', 'Ultra', 'Vivid', 'Wise',
            'Amber', 'Blaze', 'Coral', 'Dawn', 'Echo', 'Frost', 'Golden',
            'Hyper', 'Ivory', 'Lunar', 'Neon', 'Onyx', 'Prism', 'Ruby',
            'Sonic', 'Storm', 'Topaz', 'Volt', 'Zen', 'Astral', 'Cosmic',
        ];

        $nouns = [
            'Falcon', 'Panther', 'Tiger', 'Eagle', 'Hawk', 'Wolf', 'Fox',
            'Panda', 'Lion', 'Bear', 'Otter', 'Raven', 'Cobra', 'Phoenix',
            'Dragon', 'Knight', 'Voyager', 'Pioneer', 'Ranger', 'Racer',
            'Scout', 'Trader', 'Hunter', 'Pilot', 'Striker', 'Rider',
            'Warrior', 'Seeker', 'Champion', 'Ace', 'Star', 'Spark',
            'Blitz', 'Flash', 'Bolt', 'Comet', 'Nova', 'Orbit', 'Viper',
            'Jaguar', 'Lynx', 'Osprey', 'Sparrow', 'Condor', 'Mustang',
            'Stallion', 'Dolphin', 'Mantis', 'Hornet', 'Badger',
        ];

        $usedAliases = static::where('auction_id', $auctionId)
            ->whereNotNull('bidder_alias')
            ->pluck('bidder_alias')
            ->unique()
            ->toArray();

        $maxAttempts = 200;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $alias = $adjectives[array_rand($adjectives)] . $nouns[array_rand($nouns)];
            if (! in_array($alias, $usedAliases)) {
                return $alias;
            }
        }

        return 'Anon' . random_int(1000, 9999);
    }
}
