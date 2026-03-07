<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display all notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user()->load('addresses');
        
        $query = $user->notifications();
        
        // Filter by read/unread
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Build safe notification data for JS (avoid 500 from null or non-array data)
        $notificationDataForJs = $notifications->keyBy('id')->map(function ($n) {
            $data = $n->data ?? [];
            return is_array($data) ? $data : [];
        })->toArray();

        // Mark as read when viewing
        if ($request->has('mark_read')) {
            $user->unreadNotifications->markAsRead();
        }

        // Get profile completion warnings
        $profileWarnings = $this->getProfileWarnings($user);

        return view('notifications.index', compact('notifications', 'profileWarnings', 'notificationDataForJs'));
    }
    
    /**
     * Get profile completion warnings
     */
    private function getProfileWarnings($user)
    {
        $warnings = [];
        
        // Check email verification
        if (!$user->hasVerifiedEmail()) {
            $warnings[] = [
                'type' => 'email',
                'message' => 'Please verify your email address to access all features.',
                'link' => route('verification.notice'),
                'linkText' => 'Verify Email',
                'icon' => 'bi-envelope-exclamation',
                'color' => 'warning'
            ];
        }
        
        // Check for phone number
        if (empty($user->phone)) {
            $warnings[] = [
                'type' => 'phone_missing',
                'message' => 'Please add your phone number in your profile.',
                'link' => route('profile.edit'),
                'linkText' => 'Add Phone',
                'icon' => 'bi-telephone',
                'color' => 'info'
            ];
        } elseif (empty($user->phone_verified_at)) {
            $warnings[] = [
                'type' => 'phone',
                'message' => 'Please verify your phone number (PH-based) with OTP.',
                'link' => route('profile.edit'),
                'linkText' => 'Verify Phone',
                'icon' => 'bi-shield-check',
                'color' => 'warning'
            ];
        }
        
        // Check for address
        if ($user->addresses->count() === 0) {
            $warnings[] = [
                'type' => 'address',
                'message' => 'Please add your permanent/work address in your profile.',
                'link' => route('profile.edit'),
                'linkText' => 'Add Address',
                'icon' => 'bi-geo-alt',
                'color' => 'info'
            ];
        }
        
        return $warnings;
    }

    /**
     * Get unread notifications count (AJAX)
     */
    public function unreadCount()
    {
        $user = Auth::user()->load('addresses');
        $count = $user->unreadNotifications()->count();
        $profileWarnings = $this->getProfileWarnings($user);
        
        return response()->json([
            'count' => $count,
            'profileWarningsCount' => count($profileWarnings),
            'totalCount' => $count + count($profileWarnings)
        ]);
    }

    /**
     * Poll for new notifications (real-time)
     * GET ?after_id=uuid - returns notifications created after the one with this id
     * GET ?since=ISO8601 - when no notifications yet, returns notifications created after this time
     */
    public function poll(Request $request)
    {
        $afterId = $request->get('after_id');
        $since = $request->get('since');

        $query = Auth::user()->notifications();
        if ($afterId) {
            $after = Auth::user()->notifications()->find($afterId);
            if (!$after) {
                return response()->json(['notifications' => [], 'unread_count' => Auth::user()->unreadNotifications()->count()]);
            }
            $query->where('created_at', '>', $after->created_at);
        } elseif ($since && strlen($since) > 5) {
            try {
                $sinceDate = \Carbon\Carbon::parse($since);
                $query->where('created_at', '>', $sinceDate);
            } catch (\Exception $e) {
                $uc = Auth::user()->unreadNotifications()->count();
                $pw = count($this->getProfileWarnings(Auth::user()->load('addresses')));
                return response()->json(['notifications' => [], 'unread_count' => $uc, 'total_count' => $uc + $pw]);
            }
        } else {
            return response()->json(['notifications' => [], 'unread_count' => Auth::user()->unreadNotifications()->count()]);
        }

        $newNotifications = $query->orderBy('created_at', 'asc')->get();

        $user = Auth::user()->load('addresses');
        $profileWarnings = $this->getProfileWarnings($user);
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $newNotifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_raw' => $notification->created_at->toIso8601String(),
                ];
            })->values()->all(),
            'unread_count' => $unreadCount,
            'total_count' => $unreadCount + count($profileWarnings),
        ]);
    }

    /**
     * Get recent notifications (AJAX)
     */
    public function recent()
    {
        $notifications = Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'icon' => $this->getNotificationIcon($notification->type, $notification->data),
                    'color' => $this->getNotificationColor($notification->type, $notification->data),
                    'url' => $this->getNotificationUrl($notification),
                ];
            })
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications for the current user
     */
    public function destroyAll()
    {
        Auth::user()->notifications()->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get notification icon based on type
     */
    private function getNotificationIcon($type, $data = [])
    {
        $type = $data['type'] ?? $type;
        $icons = [
            'order_status' => 'bi-box-seam',
            'auction_won' => 'bi-trophy',
            'auction_outbid' => 'bi-hammer',
            'trade_offer_received' => 'bi-arrow-left-right',
            'trade_offer_accepted' => 'bi-check-circle',
            'trade_status_updated' => 'bi-arrow-repeat',
            'trade_listing_submitted' => 'bi-hourglass-split',
            'seller_approved' => 'bi-shield-check',
            'seller_rejected' => 'bi-x-circle',
            'seller_suspended' => 'bi-exclamation-triangle',
            'account_banned' => 'bi-ban',
            'account_suspended' => 'bi-pause-circle',
            'profile_update' => 'bi-person-check',
        ];
        
        return $icons[$type] ?? 'bi-bell';
    }

    /**
     * Get notification color based on type
     */
    private function getNotificationColor($type, $data = [])
    {
        $type = $data['type'] ?? $type;
        $colors = [
            'order_status' => 'primary',
            'auction_won' => 'success',
            'auction_outbid' => 'warning',
            'trade_offer_received' => 'info',
            'trade_offer_accepted' => 'success',
            'trade_status_updated' => 'info',
            'seller_approved' => 'success',
            'seller_rejected' => 'danger',
            'seller_suspended' => 'warning',
            'account_banned' => 'danger',
            'account_suspended' => 'warning',
            'profile_update' => 'primary',
        ];
        
        return $colors[$type] ?? 'secondary';
    }

    /**
     * Get notification URL based on type
     */
    private function getNotificationUrl($notification)
    {
        $data = $notification->data;
        $type = $data['type'] ?? $notification->type;
        
        switch ($type) {
            case 'order_status':
                return isset($data['order_id']) ? route('orders.show', $data['order_id']) : route('orders.index');
            
            case 'trade_offer_received':
            case 'trade_offer_accepted':
                return route('trading.conversations.index');
            
            case 'trade_status_updated':
                return route('trading.conversations.index');
            
            case 'trade_listing_submitted':
                return isset($data['listing_id']) ? route('trading.listings.show', $data['listing_id']) : route('trading.index');

            case 'trade_listing_approved':
                return isset($data['listing_id']) ? route('trading.listings.show', $data['listing_id']) : route('trading.index');

            case 'trade_listing_rejected':
                return route('trading.listings.create');
            
            case 'seller_approved':
            case 'seller_rejected':
            case 'seller_suspended':
                return route('seller.dashboard');
            
            case 'profile_update':
                return route('profile.edit');

            case 'auction_won':
            case 'auction_outbid':
                return route('notifications.index');
            
            default:
                return route('notifications.index');
        }
    }
}
