/**
 * Real-time auction updates via Laravel Echo + Reverb.
 * Loaded on auction show pages when broadcasting is enabled.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

(function () {
    if (typeof window === 'undefined' || !window.AUCTION_CONFIG || !window.AUCTION_CONFIG.echoKey) {
        return;
    }

    const config = window.AUCTION_CONFIG;

    window.Pusher = Pusher;

    if (!window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: config.echoKey,
            cluster: 'mt1',
            wsHost: config.wsHost || window.location.hostname,
            wsPort: config.wsPort || 8080,
            wssPort: config.wssPort || 443,
            forceTLS: (config.scheme || 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: config.authEndpoint || '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                },
            },
        });
    }

    const auctionId = config.auctionId;
    const userId = config.userId;

    if (!auctionId) return;

    // -- Presence channel for auction room --
    const auctionChannel = window.Echo.join('auction.' + auctionId);

    auctionChannel
        .here(function (members) {
            if (typeof window.auctionOnViewersUpdate === 'function') {
                window.auctionOnViewersUpdate(members.length);
            }
        })
        .joining(function () {
            if (typeof window.auctionOnViewerJoined === 'function') {
                window.auctionOnViewerJoined();
            }
        })
        .leaving(function () {
            if (typeof window.auctionOnViewerLeft === 'function') {
                window.auctionOnViewerLeft();
            }
        })
        .listen('.BidPlaced', function (e) {
            if (typeof window.auctionOnBidPlaced === 'function') {
                window.auctionOnBidPlaced(e);
            }
        })
        .listen('.AuctionExtended', function (e) {
            if (typeof window.auctionOnExtended === 'function') {
                window.auctionOnExtended(e);
            }
        })
        .listen('.AuctionEnded', function (e) {
            if (typeof window.auctionOnEnded === 'function') {
                window.auctionOnEnded(e);
            }
        })
        .listen('.AuctionStarted', function (e) {
            if (typeof window.auctionOnStarted === 'function') {
                window.auctionOnStarted(e);
            }
        })
        .error(function (error) {
            console.error('Auction channel error:', error);
        });

    // -- Private channel for personal notifications --
    if (userId) {
        const userChannel = window.Echo.private('auction-user.' + userId);

        userChannel
            .listen('.UserOutbid', function (e) {
                if (typeof window.auctionOnUserOutbid === 'function') {
                    window.auctionOnUserOutbid(e);
                }
            })
            .listen('.UserWonAuction', function (e) {
                if (typeof window.auctionOnUserWon === 'function') {
                    window.auctionOnUserWon(e);
                }
            })
            .error(function (error) {
                console.error('Auction user channel error:', error);
            });
    }

    // Expose cleanup for SPA-like navigation
    window.auctionEchoCleanup = function () {
        window.Echo.leave('auction.' + auctionId);
        if (userId) {
            window.Echo.leave('auction-user.' + userId);
        }
    };
})();
