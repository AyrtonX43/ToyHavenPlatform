<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Toyshop\ProductController;
use App\Http\Controllers\Toyshop\CartController;
use App\Http\Controllers\Toyshop\WishlistController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CategoryPreferenceController;
use App\Http\Controllers\SuggestedProductsController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting auth for private channels (Echo / Reverb)
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Homepage
Route::get('/', \App\Http\Controllers\HomeController::class)->name('home');

// Unified Search
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
// Real-time search suggest (products + business pages)
Route::get('/search/suggest', [\App\Http\Controllers\SearchController::class, 'suggest'])->name('search.suggest');

// PayMongo Webhook (no auth, no CSRF)
Route::post('/webhooks/paymongo', [\App\Http\Controllers\Webhook\PayMongoWebhookController::class, 'handle'])->name('webhooks.paymongo');

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// Debug route to check Google OAuth config
Route::get('/debug/google-oauth', function () {
    return [
        'client_id' => config('services.google.client_id'),
        'redirect_uri' => config('services.google.redirect'),
        'app_url' => config('app.url'),
        'env_redirect' => env('GOOGLE_REDIRECT_URI'),
        'env_app_url' => env('APP_URL'),
    ];
})->name('debug.google');

// Toyshop Routes - Public
Route::prefix('toyshop')->name('toyshop.')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
    
    // Business Pages
    Route::get('/business/{slug}', [\App\Http\Controllers\Toyshop\BusinessPageController::class, 'show'])->name('business.show');
});

// Seller business email verification (public signed URL from email link)
Route::get('/seller/verify-business-email', [\App\Http\Controllers\Seller\BusinessPageController::class, 'verifyBusinessEmail'])
    ->name('seller.business-page.verify-email')
    ->middleware('signed');

// Dashboard
Route::get('/dashboard', function () {
    // Redirect admins to admin dashboard
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard')
            ->with('info', 'Admins cannot access customer features. Please use the Admin Panel.');
    }
    
    // Check if user has selected categories, redirect if not
    if (!auth()->user()->hasSelectedCategories()) {
        return redirect()->route('category-preferences.show')
            ->with('info', 'Please select your toy category preferences to personalize your experience!');
    }
    return view('dashboard');
})->middleware(['auth', 'verified', 'redirect.admin.from.customer'])->name('dashboard');

// Authenticated Routes
Route::middleware(['auth', 'redirect.admin.from.customer'])->group(function () {
    // Category Preferences (for new users)
    Route::get('/welcome/categories', [CategoryPreferenceController::class, 'show'])->name('category-preferences.show');
    Route::post('/welcome/categories', [CategoryPreferenceController::class, 'store'])->name('category-preferences.store');
    Route::post('/welcome/categories/skip', [CategoryPreferenceController::class, 'skip'])->name('category-preferences.skip');
    
    // Suggested Products
    Route::get('/suggested-products', [SuggestedProductsController::class, 'index'])->name('suggested-products');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/category-preferences', [ProfileController::class, 'updateCategoryPreferences'])->name('profile.category-preferences.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Email check route
    Route::post('/profile/check-email', [ProfileController::class, 'checkEmail'])->name('profile.check-email');
    
    // Address Routes
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::post('/profile/addresses/{address}/set-default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.set-default');
    
    // Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::put('/update/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    });

    // Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/add', [WishlistController::class, 'add'])->name('add');
        Route::delete('/remove/{id}', [WishlistController::class, 'remove'])->name('remove');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
    });

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])->name('recent');
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/clear-all', [\App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('destroy-all');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // Checkout Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'process'])->name('process');
        Route::get('/payment/{order_number}', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'payment'])->name('payment');
        Route::post('/create-payment-intent', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'createPaymentIntent'])->name('create-payment-intent');
        Route::post('/process-payment/{order_number}', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'processPayment'])->name('process-payment');
        Route::get('/check-payment/{order_number}', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'checkPaymentStatus'])->name('check-payment');
        Route::get('/return', [\App\Http\Controllers\Toyshop\CheckoutController::class, 'paymentReturn'])->name('return');
    });

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Toyshop\OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Toyshop\OrderController::class, 'show'])->name('show');
        Route::get('/{id}/tracking', [\App\Http\Controllers\Toyshop\OrderController::class, 'tracking'])->name('tracking');
    });

    // Review Routes
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::post('/product/{productId}', [\App\Http\Controllers\Toyshop\ReviewController::class, 'storeProductReview'])->name('product.store');
        Route::post('/seller/{sellerId}', [\App\Http\Controllers\Toyshop\ReviewController::class, 'storeSellerReview'])->name('seller.store');
    });

    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\Toyshop\ReportController::class, 'showForm'])->name('create');
        Route::post('/', [\App\Http\Controllers\Toyshop\ReportController::class, 'create'])->name('store');
    });

    // Membership Routes
    Route::prefix('membership')->name('membership.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Membership\PlanController::class, 'index'])->name('index');
        Route::get('/manage', [\App\Http\Controllers\Membership\SubscriptionController::class, 'manage'])->name('manage');
        Route::post('/subscribe', [\App\Http\Controllers\Membership\SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/payment/{subscription}', [\App\Http\Controllers\Membership\SubscriptionController::class, 'payment'])->name('payment');
        Route::get('/payment-return', [\App\Http\Controllers\Membership\SubscriptionController::class, 'paymentReturn'])->name('payment-return');
        Route::post('/cancel', [\App\Http\Controllers\Membership\SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/cancel-pending/{subscription}', [\App\Http\Controllers\Membership\SubscriptionController::class, 'cancelPending'])->name('cancel-pending');
        Route::post('/process-payment/{subscription}', [\App\Http\Controllers\Membership\SubscriptionController::class, 'processPayment'])->name('process-payment');
        Route::get('/check-payment/{subscription}', [\App\Http\Controllers\Membership\SubscriptionController::class, 'checkPaymentStatus'])->name('check-payment');
    });

    // Auction Routes - Public index (teaser allowed for non-members)
    Route::prefix('auctions')->name('auctions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Auction\AuctionController::class, 'index'])->name('index');
        Route::get('/my-bids', [\App\Http\Controllers\Auction\AuctionController::class, 'myBids'])->middleware('auth')->name('my-bids');

        // Seller Verification
        Route::middleware('auth')->prefix('verification')->name('verification.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Auction\SellerVerificationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Auction\SellerVerificationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Auction\SellerVerificationController::class, 'store'])->name('store');
            Route::get('/status', [\App\Http\Controllers\Auction\SellerVerificationController::class, 'status'])->name('status');
        });

        // Seller Auction CRUD
        Route::middleware('auth')->prefix('seller')->name('seller.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Auction\SellerAuctionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Auction\SellerAuctionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Auction\SellerAuctionController::class, 'store'])->name('store');
            Route::get('/{auction}/edit', [\App\Http\Controllers\Auction\SellerAuctionController::class, 'edit'])->name('edit');
            Route::put('/{auction}', [\App\Http\Controllers\Auction\SellerAuctionController::class, 'update'])->name('update');
            Route::get('/{auction}/promote', [\App\Http\Controllers\Auction\PromotionController::class, 'show'])->name('promote');
            Route::post('/{auction}/promote', [\App\Http\Controllers\Auction\PromotionController::class, 'store'])->name('promote.store');
            Route::post('/{auction}/promote/activate', [\App\Http\Controllers\Auction\PromotionController::class, 'activate'])->name('promote.activate');
        });

        // Auction Payment (winner)
        Route::middleware('auth')->prefix('payment')->name('payment.')->group(function () {
            Route::get('/{auctionPayment}', [\App\Http\Controllers\Auction\PaymentController::class, 'show'])->name('show');
            Route::post('/{auctionPayment}/process', [\App\Http\Controllers\Auction\PaymentController::class, 'process'])->name('process');
            Route::get('/{auctionPayment}/check', [\App\Http\Controllers\Auction\PaymentController::class, 'checkStatus'])->name('check');
            Route::post('/{auctionPayment}/confirm-received', [\App\Http\Controllers\Auction\PaymentController::class, 'confirmReceived'])->name('confirm-received');
        });

        // Wallet
        Route::middleware('auth')->prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Auction\WalletController::class, 'index'])->name('index');
            Route::post('/keep-deposit', [\App\Http\Controllers\Auction\WalletController::class, 'keepDeposit'])->name('keep-deposit');
        });

        // Live room
        Route::get('/{auction}/live', [\App\Http\Controllers\Auction\AuctionController::class, 'liveRoom'])->name('live-room');

        Route::get('/{auction}', [\App\Http\Controllers\Auction\AuctionController::class, 'show'])->name('show');
        Route::post('/{auction}/bids', [\App\Http\Controllers\Auction\BidController::class, 'store'])->middleware(['auth', 'membership'])->name('bids.store');
    });

    // Seller Routes
    Route::prefix('seller')->name('seller.')->middleware(['role:seller,admin', 'seller.approved'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Seller\DashboardController::class, 'index'])->name('dashboard');
        
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\ProductController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Seller\ProductController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Seller\ProductController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Seller\ProductController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Seller\ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Seller\ProductController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Seller\ProductController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\OrderController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Seller\OrderController::class, 'show'])->name('show');
            Route::put('/{id}/status', [\App\Http\Controllers\Seller\OrderController::class, 'updateStatus'])->name('updateStatus');
            Route::put('/bulk-update', [\App\Http\Controllers\Seller\OrderController::class, 'bulkUpdate'])->name('bulkUpdate');
        });

        Route::prefix('business-page')->name('business-page.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\BusinessPageController::class, 'index'])->name('index');
            Route::post('/settings', [\App\Http\Controllers\Seller\BusinessPageController::class, 'updateSettings'])->name('settings.update');
            Route::post('/contact', [\App\Http\Controllers\Seller\BusinessPageController::class, 'updateContact'])->name('contact.update');
            Route::post('/social-links', [\App\Http\Controllers\Seller\BusinessPageController::class, 'updateSocialLinks'])->name('social-links.update');
            Route::post('/payment-qr', [\App\Http\Controllers\Seller\BusinessPageController::class, 'updatePaymentQr'])->name('payment-qr.update');
            Route::get('/preview', [\App\Http\Controllers\Seller\BusinessPageController::class, 'preview'])->name('preview');
        });

        // POS - Only for approved sellers (handled in controller)
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\PosController::class, 'index'])->name('index');
            Route::post('/process', [\App\Http\Controllers\Seller\PosController::class, 'processOrder'])->name('process');
        });

        // Shop Upgrade - Available to all sellers
        Route::prefix('shop-upgrade')->name('shop-upgrade.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Seller\ShopUpgradeController::class, 'index'])->name('index');
            Route::post('/submit', [\App\Http\Controllers\Seller\ShopUpgradeController::class, 'submitUpgrade'])->name('submit');
            Route::post('/upload-document', [\App\Http\Controllers\Seller\ShopUpgradeController::class, 'uploadDocument'])->name('upload-document');
        });
    });

    // Seller Registration (accessible to all authenticated users)
    Route::get('/seller/register', [\App\Http\Controllers\Seller\RegistrationController::class, 'show'])->name('seller.register');
    Route::post('/seller/register', [\App\Http\Controllers\Seller\RegistrationController::class, 'store'])->name('seller.register.store');

    // Amazon Search API (for product forms)
    Route::prefix('api/amazon')->name('api.amazon.')->group(function () {
        Route::post('/search-url', [\App\Http\Controllers\Api\AmazonSearchController::class, 'searchByUrl'])->name('search-url');
        Route::post('/search-name', [\App\Http\Controllers\Api\AmazonSearchController::class, 'searchByName'])->name('search-name');
    });

    // Price Calculation API (for price adjustment with commission and taxes)
    Route::prefix('api/price')->name('api.price.')->group(function () {
        Route::post('/calculate', [\App\Http\Controllers\Api\PriceCalculationController::class, 'calculate'])->name('calculate');
        Route::post('/calculate-reverse', [\App\Http\Controllers\Api\PriceCalculationController::class, 'calculateReverse'])->name('calculate-reverse');
    });

    // Trading Routes - Public
    Route::prefix('trading')->name('trading.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Trading\TradeListingController::class, 'index'])->name('index');
    });

    // Trading Routes - Authenticated
    Route::middleware(['auth'])->prefix('trading')->name('trading.')->group(function () {
        // Trade Listings - Specific routes must come before parameterized routes
        Route::get('/listings', [\App\Http\Controllers\Trading\TradeListingController::class, 'myListings'])->name('listings.my');
        Route::get('/listings/create', [\App\Http\Controllers\Trading\TradeListingController::class, 'create'])->name('listings.create');
        Route::post('/listings', [\App\Http\Controllers\Trading\TradeListingController::class, 'store'])->name('listings.store');
        Route::get('/listings/{id}/edit', [\App\Http\Controllers\Trading\TradeListingController::class, 'edit'])->name('listings.edit');
        Route::put('/listings/{id}', [\App\Http\Controllers\Trading\TradeListingController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{id}', [\App\Http\Controllers\Trading\TradeListingController::class, 'destroy'])->name('listings.destroy');
    });

    // Trading Routes - Public (show listing)
    Route::prefix('trading')->name('trading.')->group(function () {
        Route::get('/listings/{id}', [\App\Http\Controllers\Trading\TradeListingController::class, 'show'])->name('listings.show');
    });

    // Trading Routes - Authenticated (continued)
    Route::middleware(['auth'])->prefix('trading')->name('trading.')->group(function () {
        // Trade Offers
        Route::post('/listings/{id}/offers', [\App\Http\Controllers\Trading\TradeOfferController::class, 'store'])->name('offers.store');
        Route::get('/offers', [\App\Http\Controllers\Trading\TradeOfferController::class, 'myOffers'])->name('offers.my');
        Route::get('/offers/received', [\App\Http\Controllers\Trading\TradeOfferController::class, 'offersOnMyListings'])->name('offers.received');
        Route::get('/offers/{id}', [\App\Http\Controllers\Trading\TradeOfferController::class, 'show'])->name('offers.show');
        Route::post('/offers/{id}/accept', [\App\Http\Controllers\Trading\TradeOfferController::class, 'accept'])->name('offers.accept');
        Route::post('/offers/{id}/reject', [\App\Http\Controllers\Trading\TradeOfferController::class, 'reject'])->name('offers.reject');
        Route::post('/offers/{id}/counter', [\App\Http\Controllers\Trading\TradeOfferController::class, 'counterOffer'])->name('offers.counter');
        Route::post('/offers/{id}/withdraw', [\App\Http\Controllers\Trading\TradeOfferController::class, 'withdraw'])->name('offers.withdraw');
        
        // Trades
        Route::get('/trades', [\App\Http\Controllers\Trading\TradeController::class, 'index'])->name('trades.index');
        Route::get('/trades/{id}', [\App\Http\Controllers\Trading\TradeController::class, 'show'])->name('trades.show');
        Route::put('/trades/{id}/shipping', [\App\Http\Controllers\Trading\TradeController::class, 'updateShipping'])->name('trades.update-shipping');
        Route::post('/trades/{id}/shipped', [\App\Http\Controllers\Trading\TradeController::class, 'markShipped'])->name('trades.mark-shipped');
        Route::post('/trades/{id}/received', [\App\Http\Controllers\Trading\TradeController::class, 'markReceived'])->name('trades.mark-received');
        Route::post('/trades/{id}/complete', [\App\Http\Controllers\Trading\TradeController::class, 'complete'])->name('trades.complete');
        Route::post('/trades/{id}/dispute', [\App\Http\Controllers\Trading\TradeController::class, 'dispute'])->name('trades.dispute');
        Route::post('/trades/{id}/cancel', [\App\Http\Controllers\Trading\TradeController::class, 'cancel'])->name('trades.cancel');
        
        // Conversations (trade chat)
        Route::get('/conversations/unread', [\App\Http\Controllers\Trading\ConversationController::class, 'unreadCount'])->name('conversations.unread');
        Route::get('/conversations', [\App\Http\Controllers\Trading\ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversations/{conversation}', [\App\Http\Controllers\Trading\ConversationController::class, 'show'])->name('conversations.show');
        Route::get('/listings/{id}/conversation', [\App\Http\Controllers\Trading\ConversationController::class, 'storeFromListing'])->name('conversations.store-from-listing');
        Route::post('/listings/{id}/conversation', [\App\Http\Controllers\Trading\ConversationController::class, 'storeFromListing'])->name('conversations.store-from-listing.post');
        Route::get('/conversations/{conversation}/messages', [\App\Http\Controllers\Trading\ConversationController::class, 'getMessages'])->name('conversations.messages.index');
        Route::post('/conversations/{conversation}/messages', [\App\Http\Controllers\Trading\ConversationController::class, 'sendMessage'])->name('conversations.messages.store');
        Route::post('/conversations/{conversation}/delivered', [\App\Http\Controllers\Trading\ConversationController::class, 'markDelivered'])->name('conversations.mark-delivered');
        Route::post('/conversations/{conversation}/seen', [\App\Http\Controllers\Trading\ConversationController::class, 'markSeen'])->name('conversations.mark-seen');
        Route::post('/conversations/{conversation}/typing', [\App\Http\Controllers\Trading\ConversationController::class, 'typing'])->name('conversations.typing');
        Route::post('/conversations/{conversation}/presence', [\App\Http\Controllers\Trading\ConversationController::class, 'presence'])->name('conversations.presence');
        Route::get('/conversations/{conversation}/other-status', [\App\Http\Controllers\Trading\ConversationController::class, 'otherStatus'])->name('conversations.other-status');
        Route::get('/conversations/{conversation}/message-statuses', [\App\Http\Controllers\Trading\ConversationController::class, 'messageStatuses'])->name('conversations.message-statuses');
        Route::get('/conversations/{conversation}/typing-status', [\App\Http\Controllers\Trading\ConversationController::class, 'typingStatus'])->name('conversations.typing-status');
        Route::delete('/conversations/{conversation}/messages/{message}', [\App\Http\Controllers\Trading\ConversationController::class, 'unsendMessage'])->name('conversations.messages.unsend');
        Route::get('/conversations/{conversation}/report', [\App\Http\Controllers\Trading\ConversationController::class, 'reportForm'])->name('conversations.report-form');
        Route::post('/conversations/{conversation}/report', [\App\Http\Controllers\Trading\ConversationController::class, 'report'])->name('conversations.report');
        
        // User Products
        Route::get('/my-products', [\App\Http\Controllers\Trading\UserProductController::class, 'index'])->name('products.index');
        Route::get('/my-products/create', [\App\Http\Controllers\Trading\UserProductController::class, 'create'])->name('products.create');
        Route::post('/my-products', [\App\Http\Controllers\Trading\UserProductController::class, 'store'])->name('products.store');
        Route::get('/my-products/{id}', [\App\Http\Controllers\Trading\UserProductController::class, 'show'])->name('products.show');
        Route::get('/my-products/{id}/edit', [\App\Http\Controllers\Trading\UserProductController::class, 'edit'])->name('products.edit');
        Route::put('/my-products/{id}', [\App\Http\Controllers\Trading\UserProductController::class, 'update'])->name('products.update');
        Route::delete('/my-products/{id}', [\App\Http\Controllers\Trading\UserProductController::class, 'destroy'])->name('products.destroy');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        Route::prefix('conversation-reports')->name('conversation-reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ConversationReportController::class, 'index'])->name('index');
            Route::get('/{report}', [\App\Http\Controllers\Admin\ConversationReportController::class, 'show'])->name('show');
            Route::put('/{report}', [\App\Http\Controllers\Admin\ConversationReportController::class, 'update'])->name('update');
        });

        Route::prefix('business-page-revisions')->name('business-page-revisions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BusinessPageRevisionController::class, 'index'])->name('index');
            Route::get('/{revision}', [\App\Http\Controllers\Admin\BusinessPageRevisionController::class, 'show'])->name('show');
            Route::post('/{revision}/approve', [\App\Http\Controllers\Admin\BusinessPageRevisionController::class, 'approve'])->name('approve');
            Route::post('/{revision}/reject', [\App\Http\Controllers\Admin\BusinessPageRevisionController::class, 'reject'])->name('reject');
        });

        Route::prefix('sellers')->name('sellers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SellerController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\SellerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'update'])->name('update');
            Route::post('/{id}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'reject'])->name('reject');
            Route::post('/{id}/suspend', [\App\Http\Controllers\Admin\SellerController::class, 'suspend'])->name('suspend');
            Route::post('/{id}/activate', [\App\Http\Controllers\Admin\SellerController::class, 'activate'])->name('activate');
            
            // Document management routes
            Route::post('/{sellerId}/documents/{documentId}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approveDocument'])->name('documents.approve');
            Route::post('/{sellerId}/documents/{documentId}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'rejectDocument'])->name('documents.reject');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
            Route::get('/pending', [\App\Http\Controllers\Admin\ProductController::class, 'pendingOrganizer'])->name('pending');
            Route::get('/approved', [\App\Http\Controllers\Admin\ProductController::class, 'approvedOrganizer'])->name('approved');
            Route::get('/rejected', [\App\Http\Controllers\Admin\ProductController::class, 'rejectedOrganizer'])->name('rejected');
            Route::get('/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
            Route::post('/{id}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Admin\ProductController::class, 'reject'])->name('reject');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [\App\Http\Controllers\Admin\ProductController::class, 'bulkAction'])->name('bulk-action');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\ReportController::class, 'show'])->name('show');
            Route::post('/{id}/review', [\App\Http\Controllers\Admin\ReportController::class, 'review'])->name('review');
            Route::post('/{id}/resolve', [\App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('resolve');
            Route::post('/{id}/dismiss', [\App\Http\Controllers\Admin\ReportController::class, 'dismiss'])->name('dismiss');
        });
        
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('show');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
            Route::post('/{id}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('ban');
            Route::post('/{id}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('unban');
        });

        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('index');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
        });

        Route::prefix('auctions')->name('auctions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AuctionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\AuctionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AuctionController::class, 'store'])->name('store');
            Route::get('/{auction}', [\App\Http\Controllers\Admin\AuctionController::class, 'show'])->name('show');
            Route::get('/{auction}/edit', [\App\Http\Controllers\Admin\AuctionController::class, 'edit'])->name('edit');
            Route::put('/{auction}', [\App\Http\Controllers\Admin\AuctionController::class, 'update'])->name('update');
            Route::post('/{auction}/approve', [\App\Http\Controllers\Admin\AuctionController::class, 'approve'])->name('approve');
            Route::post('/{auction}/reject', [\App\Http\Controllers\Admin\AuctionController::class, 'reject'])->name('reject');
            Route::post('/{auction}/cancel', [\App\Http\Controllers\Admin\AuctionController::class, 'cancel'])->name('cancel');
            Route::delete('/{auction}', [\App\Http\Controllers\Admin\AuctionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('auction-verifications')->name('auction-verifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AuctionVerificationController::class, 'index'])->name('index');
            Route::get('/{verification}', [\App\Http\Controllers\Admin\AuctionVerificationController::class, 'show'])->name('show');
            Route::post('/{verification}/approve', [\App\Http\Controllers\Admin\AuctionVerificationController::class, 'approve'])->name('approve');
            Route::post('/{verification}/reject', [\App\Http\Controllers\Admin\AuctionVerificationController::class, 'reject'])->name('reject');
            Route::post('/{verification}/resubmission', [\App\Http\Controllers\Admin\AuctionVerificationController::class, 'requestResubmission'])->name('resubmission');
        });

        Route::prefix('auction-sellers')->name('auction-sellers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AuctionSellerController::class, 'index'])->name('index');
            Route::get('/{seller}', [\App\Http\Controllers\Admin\AuctionSellerController::class, 'show'])->name('show');
            Route::put('/{seller}/update-name', [\App\Http\Controllers\Admin\AuctionSellerController::class, 'updateBusinessName'])->name('update-name');
            Route::post('/{seller}/suspend', [\App\Http\Controllers\Admin\AuctionSellerController::class, 'suspend'])->name('suspend');
            Route::post('/{seller}/activate', [\App\Http\Controllers\Admin\AuctionSellerController::class, 'activate'])->name('activate');
        });

        Route::prefix('auction-payments')->name('auction-payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AuctionPaymentAdminController::class, 'index'])->name('index');
            Route::get('/{auctionPayment}', [\App\Http\Controllers\Admin\AuctionPaymentAdminController::class, 'show'])->name('show');
            Route::post('/{auctionPayment}/release-escrow', [\App\Http\Controllers\Admin\AuctionPaymentAdminController::class, 'releaseEscrow'])->name('release-escrow');
            Route::post('/{auctionPayment}/refund', [\App\Http\Controllers\Admin\AuctionPaymentAdminController::class, 'refund'])->name('refund');
        });

        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('index');
            Route::get('/{plan}/edit', [\App\Http\Controllers\Admin\PlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('update');
        });

        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
        });

        Route::prefix('trades')->name('trades.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\TradeController::class, 'index'])->name('index');
            Route::get('/listings', [\App\Http\Controllers\Admin\TradeController::class, 'listings'])->name('listings');
            Route::get('/listings/approved', [\App\Http\Controllers\Admin\TradeController::class, 'approvedListings'])->name('listings.approved');
            Route::get('/listings/rejected', [\App\Http\Controllers\Admin\TradeController::class, 'rejectedListings'])->name('listings.rejected');
            Route::get('/listings/{id}', [\App\Http\Controllers\Admin\TradeController::class, 'showListing'])->name('listings.show');
            Route::delete('/listings/{id}', [\App\Http\Controllers\Admin\TradeController::class, 'deleteListing'])->name('delete-listing');
            Route::post('/listings/{id}/approve', [\App\Http\Controllers\Admin\TradeController::class, 'approveListing'])->name('approve-listing');
            Route::post('/listings/{id}/reject', [\App\Http\Controllers\Admin\TradeController::class, 'rejectListing'])->name('reject-listing');
            Route::get('/{id}', [\App\Http\Controllers\Admin\TradeController::class, 'show'])->name('show');
            Route::post('/{id}/resolve-dispute', [\App\Http\Controllers\Admin\TradeController::class, 'resolveDispute'])->name('resolve-dispute');
            Route::post('/{id}/cancel', [\App\Http\Controllers\Admin\TradeController::class, 'cancel'])->name('cancel');
        });
    });
});

require __DIR__.'/auth.php';
