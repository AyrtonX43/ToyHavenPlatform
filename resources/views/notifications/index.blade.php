@extends('layouts.toyshop')

@section('title', 'Notifications - ToyHaven')

@push('styles')
<style>
    .notifications-page {
        min-height: 60vh;
    }
    
    .notifications-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
        color: white;
        padding: 2.25rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 24px 24px;
        box-shadow: 0 12px 48px rgba(15, 23, 42, 0.25);
    }
    
    .notifications-header h1 {
        font-weight: 700;
        letter-spacing: -0.025em;
        font-size: 1.75rem;
    }
    
    .notifications-header .opacity-90 {
        opacity: 0.9;
        font-size: 0.95rem;
    }
    
    .notifications-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    .notification-filters {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .filter-btn:hover {
        background: #f8fafc;
        border-color: #0d9488;
        color: #0d9488;
    }
    
    .filter-btn.active {
        background: #0f172a;
        color: white;
        border-color: #0f172a;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
    }
    
    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .notification-item {
        background: white;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        border: 1px solid #f1f5f9;
        position: relative;
        overflow: hidden;
    }
    
    .notification-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        border-color: #e2e8f0;
    }
    
    .notification-item.unread {
        background: linear-gradient(to right, #f0fdfa 0%, #ffffff 100%);
        border-left: 4px solid #0d9488;
    }
    
    .notification-item.unread .notification-dot {
        display: block;
    }
    
    .notification-dot {
        display: none;
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: #0d9488;
        border-radius: 50%;
        z-index: 1;
    }
    
    .notification-item-link {
        display: flex;
        align-items: flex-start;
        padding: 1rem 1.25rem 1rem 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: background 0.15s ease;
    }
    
    .notification-item-link:hover {
        background: #fafbfc;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.2rem;
        line-height: 1.4;
    }
    
    .notification-message {
        font-size: 0.8125rem;
        color: #64748b;
        margin-bottom: 0.3rem;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .notification-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .notification-delete-btn {
        position: absolute;
        top: 0.625rem;
        right: 0.625rem;
        padding: 0.35rem 0.45rem;
        border: none;
        background: transparent;
        color: #94a3b8;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    
    .notification-delete-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .empty-state {
        text-align: center;
        padding: 3.5rem 2rem;
        background: white;
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
    }
    
    .empty-state-icon {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    
    .empty-state h4 {
        font-size: 1.125rem;
        color: #334155;
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .mark-all-read-btn {
        background: #0d9488;
        color: white;
    }
    
    .mark-all-read-btn:hover {
        background: #0f766e;
        color: white;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.3);
    }
    
    .clear-all-btn {
        background: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    
    .clear-all-btn:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .profile-reminder-section {
        background: linear-gradient(to right, #fffbeb 0%, #ffffff 100%);
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    
    .profile-reminder-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #fde68a;
    }
    
    .profile-reminder-item {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        transition: all 0.25s ease;
        border: 1px solid #f1f5f9;
        border-left-width: 4px;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .profile-reminder-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        text-decoration: none;
        color: inherit;
    }
    
    .profile-reminder-item.warning {
        border-left: 4px solid #f59e0b;
        background: #fffbeb;
    }
    
    .profile-reminder-item.info {
        border-left: 4px solid #3b82f6;
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
<div class="notifications-page">
<div class="notifications-header">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="mb-2">
                    <i class="bi bi-bell me-2"></i>Notifications
                </h1>
                <p class="mb-0 opacity-90">Stay updated with your account activity</p>
            </div>
            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
                $totalCount = auth()->user()->notifications()->count();
            @endphp
            <div class="notifications-toolbar">
                @if($unreadCount > 0)
                    <button type="button" class="btn-action mark-all-read-btn" onclick="markAllAsRead()">
                        <i class="bi bi-check-all"></i> Mark All Read
                    </button>
                @endif
                @if($totalCount > 0)
                    <button type="button" class="btn-action clear-all-btn" onclick="clearAllNotifications()">
                        <i class="bi bi-trash"></i> Clear All
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="notification-filters">
        <a href="{{ route('notifications.index') }}" class="filter-btn {{ !request('filter') ? 'active' : '' }}">
            <i class="bi bi-list-ul me-2"></i>All
        </a>
        @php $totalUnread = $unreadCount + count($profileWarnings ?? []); @endphp
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="filter-btn {{ request('filter') === 'unread' ? 'active' : '' }}">
            <i class="bi bi-circle-fill me-2"></i>Unread ({{ $totalUnread }})
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
                
                <div class="notification-item {{ $isUnread ? 'unread' : '' }}" data-notification-id="{{ $notification->id }}">
                    <span class="notification-dot"></span>
                    <a href="{{ $url }}" class="notification-item-link" onclick="markAsRead('{{ $notification->id }}', event)">
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
                                <span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">New</span>
                            @endif
                        </div>
                    </a>
                    <button type="button" class="notification-delete-btn" onclick="deleteNotification('{{ $notification->id }}', event)" title="Delete notification" aria-label="Delete notification">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-bell-slash empty-state-icon"></i>
            <h4 class="fw-bold mb-2">No notifications</h4>
            <p class="text-muted mb-0">You're all caught up! We'll notify you when there's something new.</p>
        </div>
    @endif
</div>
</div>

@push('scripts')
<script>
    function deleteNotification(notificationId, event) {
        event.preventDefault();
        event.stopPropagation();
        if (!confirm('Are you sure you want to delete this notification? This cannot be undone.')) {
            return;
        }
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => item.remove(), 300);
                }
                if (typeof showFlashNotification === 'function') {
                    showFlashNotification('Notification deleted successfully.', 'success');
                } else {
                    alert('Notification deleted successfully.');
                }
                updateNotificationCount();
            } else {
                if (typeof showFlashNotification === 'function') {
                    showFlashNotification('Unable to delete notification. Please try again.', 'error');
                } else {
                    alert('Unable to delete notification.');
                }
            }
        }).catch(() => {
            if (typeof showFlashNotification === 'function') {
                showFlashNotification('Something went wrong. Please try again.', 'error');
            } else {
                alert('Something went wrong.');
            }
        });
    }
    
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
            const item = event.currentTarget.closest('.notification-item');
            if (item) {
                item.classList.remove('unread');
                const badge = item.querySelector('.badge');
                if (badge) badge.remove();
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.style.display = 'none';
            }
            
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
    
    function clearAllNotifications() {
        if (!confirm('Are you sure you want to delete ALL notifications? This cannot be undone.')) {
            return;
        }
        fetch('/notifications/clear-all', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                if (typeof showFlashNotification === 'function') {
                    showFlashNotification('All notifications have been deleted.', 'success');
                }
                updateNotificationCount();
                location.reload();
            } else {
                if (typeof showFlashNotification === 'function') {
                    showFlashNotification('Unable to clear notifications. Please try again.', 'error');
                }
            }
        }).catch(() => {
            if (typeof showFlashNotification === 'function') {
                showFlashNotification('Unable to clear notifications. Please try again.', 'error');
            }
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
