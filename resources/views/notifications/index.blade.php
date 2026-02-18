@extends('layouts.toyshop')

@section('title', 'Notifications - ToyHaven')

@push('styles')
<style>
    .notifications-header {
        background: #0f172a;
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 20px 20px;
    }
    
    .notification-filters {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        border: 2px solid #e5e7eb;
        background: white;
        color: #64748b;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: #0f172a;
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
    }
    
    .notification-item {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        cursor: pointer;
        position: relative;
    }
    
    .notification-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .notification-item.unread {
        background: #f0fdfa;
        border-left-color: #0d9488;
    }
    
    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background: #0d9488;
        border-radius: 50%;
    }
    
    .notification-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .notification-message {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: auto;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    
    .mark-all-read-btn {
        background: #0f172a;
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .mark-all-read-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
    }
    
    .profile-reminder-section {
        background: #fffbf0;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .profile-reminder-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #fde68a;
    }
    
    .profile-reminder-item {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        border-left: 4px solid;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .profile-reminder-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        text-decoration: none;
        color: inherit;
    }
    
    .profile-reminder-item.warning {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }
    
    .profile-reminder-item.info {
        border-left-color: #3b82f6;
        background: #eff6ff;
    }
    
    .profile-reminder-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .profile-reminder-content {
        flex: 1;
    }
    
    .profile-reminder-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .profile-reminder-action {
        font-size: 0.875rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="notifications-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2">
                    <i class="bi bi-bell me-2"></i>Notifications
                </h1>
                <p class="mb-0 opacity-90">Stay updated with your account activity</p>
            </div>
            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
                $totalUnread = $unreadCount + count($profileWarnings ?? []);
            @endphp
            @if($unreadCount > 0)
                <button class="mark-all-read-btn" onclick="markAllAsRead()">
                    <i class="bi bi-check-all me-2"></i>Mark All as Read
                </button>
            @endif
        </div>
    </div>
</div>

<div class="container">
    <div class="notification-filters">
        <a href="{{ route('notifications.index') }}" class="filter-btn {{ !request('filter') ? 'active' : '' }}">
            <i class="bi bi-list-ul me-2"></i>All
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="filter-btn {{ request('filter') === 'unread' ? 'active' : '' }}">
            <i class="bi bi-circle-fill me-2"></i>Unread ({{ $unreadCount + count($profileWarnings ?? []) }})
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'read']) }}" class="filter-btn {{ request('filter') === 'read' ? 'active' : '' }}">
            <i class="bi bi-check-circle me-2"></i>Read
        </a>
    </div>

    @if(isset($profileWarnings) && count($profileWarnings) > 0 && request('filter') !== 'read')
        <div class="profile-reminder-section">
            <div class="profile-reminder-header">
                <div>
                    <h5 class="mb-1 fw-bold">
                        <i class="bi bi-exclamation-circle me-2 text-warning"></i>Profile Reminders
                    </h5>
                    <p class="mb-0 small text-muted">Complete your profile to access all features</p>
                </div>
            </div>
            <div class="profile-reminders-list">
                @foreach($profileWarnings as $warning)
                    <a href="{{ $warning['link'] }}" class="profile-reminder-item {{ $warning['color'] }}">
                        <div class="d-flex align-items-start">
                            <div class="profile-reminder-icon bg-{{ $warning['color'] }} text-white">
                                <i class="bi {{ $warning['icon'] }}"></i>
                            </div>
                            <div class="profile-reminder-content">
                                <div class="profile-reminder-title">{{ $warning['message'] }}</div>
                                <div class="profile-reminder-action text-{{ $warning['color'] }}">
                                    <i class="bi bi-arrow-right-circle me-1"></i>{{ $warning['linkText'] }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $warning['color'] }} rounded-pill ms-2" style="font-size: 0.7rem; padding: 0.4em 0.8em;">Action Required</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($notifications->count() > 0)
        <div class="notifications-list">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? $notification->type;
                    $isUnread = is_null($notification->read_at);
                    
                    // Get icon and color
                    $icons = [
                        'order_status' => 'bi-box-seam',
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
                    
                    $colors = [
                        'order_status' => 'primary',
                        'trade_offer_received' => 'info',
                        'trade_offer_accepted' => 'success',
                        'trade_status_updated' => 'info',
                        'trade_listing_submitted' => 'warning',
                        'seller_approved' => 'success',
                        'seller_rejected' => 'danger',
                        'seller_suspended' => 'warning',
                        'account_banned' => 'danger',
                        'account_suspended' => 'warning',
                        'profile_update' => 'primary',
                    ];
                    
                    $icon = $icons[$type] ?? 'bi-bell';
                    $color = $colors[$type] ?? 'secondary';
                    
                    // Get URL
                    $url = '#';
                    switch ($type) {
                        case 'order_status':
                            $url = isset($data['order_id']) ? route('orders.show', $data['order_id']) : route('orders.index');
                            break;
                        case 'trade_offer_received':
                        case 'trade_offer_accepted':
                            $url = isset($data['offer_id']) ? route('trading.offers.show', $data['offer_id']) : route('trading.offers.received');
                            break;
                        case 'trade_status_updated':
                            $url = isset($data['trade_id']) ? route('trading.trades.show', $data['trade_id']) : route('trading.trades.index');
                            break;
                        case 'trade_listing_submitted':
                            $url = isset($data['listing_id']) ? route('trading.listings.show', $data['listing_id']) : route('trading.index');
                            break;
                        case 'seller_approved':
                        case 'seller_rejected':
                        case 'seller_suspended':
                            $url = route('seller.dashboard');
                            break;
                        case 'profile_update':
                            $url = route('profile.edit');
                            break;
                    }
                @endphp
                
                <a href="{{ $url }}" class="notification-item {{ $isUnread ? 'unread' : '' }}" onclick="markAsRead('{{ $notification->id }}', event)">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon bg-{{ $color }} text-white">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">{{ $data['message'] ?? 'New notification' }}</div>
                            @if(isset($data['listing_title']))
                                <div class="notification-message">{{ $data['listing_title'] }}</div>
                            @endif
                            <div class="notification-time">
                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="notification-actions">
                            @if($isUnread)
                                <span class="badge bg-primary">New</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-bell-slash empty-state-icon"></i>
            <h4 class="fw-bold mb-2">No notifications</h4>
            <p class="text-muted">You're all caught up! We'll notify you when there's something new.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function markAsRead(notificationId, event) {
        // Don't prevent default if it's already read
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            // Update UI
            const item = event.currentTarget;
            item.classList.remove('unread');
            const badge = item.querySelector('.badge');
            if (badge) badge.remove();
            
            // Update count
            updateNotificationCount();
        });
    }
    
    function markAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            location.reload();
        });
    }
    
    function updateNotificationCount() {
        fetch('/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const totalCount = data.totalCount || (data.count + (data.profileWarningsCount || 0));
                
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (totalCount > 0) {
                        badge.textContent = totalCount > 99 ? '99+' : totalCount;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
    }
</script>
@endpush
@endsection
