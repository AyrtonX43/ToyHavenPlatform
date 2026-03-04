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
        cursor: pointer;
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
    
    .notification-item-content {
        display: flex;
        align-items: flex-start;
        padding: 1rem 1.25rem 1rem 1.5rem;
        text-decoration: none;
        color: inherit;
        transition: background 0.15s ease;
    }
    
    .notification-item-content:hover {
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
        z-index: 10;
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

    /* Full notification detail view */
    .notification-detail-view {
        background: white;
        border-radius: 14px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    .notification-detail-header {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .notification-detail-icon {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .notification-detail-header-content {
        flex: 1;
    }

    .notification-detail-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .notification-detail-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: #64748b;
    }

    .notification-detail-body {
        margin-bottom: 1.5rem;
    }

    .notification-detail-section {
        margin-bottom: 1.5rem;
    }

    .notification-detail-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notification-detail-text {
        font-size: 0.9375rem;
        color: #475569;
        line-height: 1.7;
        white-space: pre-wrap;
    }

    .notification-detail-box {
        background: #f8fafc;
        border-left: 4px solid #cbd5e1;
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .notification-detail-box.danger {
        background: #fef2f2;
        border-left-color: #dc2626;
    }

    .notification-detail-box.warning {
        background: #fffbeb;
        border-left-color: #f59e0b;
    }

    .notification-detail-box.success {
        background: #f0fdf4;
        border-left-color: #10b981;
    }

    .notification-detail-box.info {
        background: #eff6ff;
        border-left-color: #3b82f6;
    }

    .notification-detail-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .notification-detail-btn {
        padding: 0.625rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .notification-detail-btn-primary {
        background: #0ea5e9;
        color: white;
    }

    .notification-detail-btn-primary:hover {
        background: #0284c7;
        color: white;
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
    }

    .notification-detail-btn-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    .notification-detail-btn-secondary:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .notification-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .notification-info-item {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .notification-info-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .notification-info-value {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1e293b;
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
        <div class="notifications-list" id="notifications-list-container" data-latest-id="{{ $notifications->first()->id }}">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? $notification->type;
                    $isUnread = is_null($notification->read_at);
                    
                    // Get icon and color
                    $icons = [
                        'order_status' => 'bi-box-seam',
                        'trade_offer_received' => 'bi-arrow-left-right',
                        'trade_offer_rejected' => 'bi-x-circle',
                        'trade_offer_accepted' => 'bi-check-circle',
                        'trade_status_updated' => 'bi-arrow-repeat',
                        'trade_listing_submitted' => 'bi-hourglass-split',
                        'trade_listing_approved' => 'bi-check-circle',
                        'trade_listing_rejected' => 'bi-x-circle',
                        'seller_approved' => 'bi-shield-check',
                        'seller_rejected' => 'bi-x-circle',
                        'seller_suspended' => 'bi-exclamation-triangle',
                        'account_banned' => 'bi-ban',
                        'account_suspended' => 'bi-pause-circle',
                        'profile_update' => 'bi-person-check',
                        'auction_won' => 'bi-trophy',
                        'auction_outbid' => 'bi-hammer',
                        'order_shipped' => 'bi-truck',
                        'order_delivered' => 'bi-check-circle',
                        'payment_success' => 'bi-credit-card-2-front',
                        'product_approved' => 'bi-check-circle',
                        'product_rejected' => 'bi-x-circle',
                        'document_rejected' => 'bi-file-earmark-x',
                        'business_page_revision_approved' => 'bi-check-circle',
                        'business_page_revision_rejected' => 'bi-x-circle',
                    ];
                    
                    $colors = [
                        'order_status' => 'primary',
                        'trade_offer_received' => 'info',
                        'trade_offer_rejected' => 'danger',
                        'trade_offer_accepted' => 'success',
                        'trade_status_updated' => 'info',
                        'trade_listing_submitted' => 'warning',
                        'trade_listing_approved' => 'success',
                        'trade_listing_rejected' => 'danger',
                        'seller_approved' => 'success',
                        'seller_rejected' => 'danger',
                        'seller_suspended' => 'warning',
                        'account_banned' => 'danger',
                        'account_suspended' => 'warning',
                        'profile_update' => 'primary',
                        'auction_won' => 'success',
                        'auction_outbid' => 'warning',
                        'order_shipped' => 'info',
                        'order_delivered' => 'success',
                        'payment_success' => 'success',
                        'product_approved' => 'success',
                        'product_rejected' => 'danger',
                        'document_rejected' => 'danger',
                        'business_page_revision_approved' => 'success',
                        'business_page_revision_rejected' => 'danger',
                    ];
                    
                    $icon = $icons[$type] ?? 'bi-bell';
                    $color = $colors[$type] ?? 'secondary';
                @endphp
                
                <div class="notification-item {{ $isUnread ? 'unread' : '' }}" data-notification-id="{{ $notification->id }}" onclick="viewNotification('{{ $notification->id }}', event)">
                    <span class="notification-dot"></span>
                    <div class="notification-item-content">
                        <div class="notification-icon bg-{{ $color }} text-white">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title fw-bold">{{ $data['title'] ?? $data['message'] ?? 'New notification' }}</div>
                            @if(isset($data['business_name']))
                                <div class="notification-message text-muted">
                                    <i class="bi bi-shop me-1"></i>{{ $data['business_name'] }}
                                </div>
                            @endif
                            @if(isset($data['message']) && isset($data['title']) && $data['message'] !== $data['title'])
                                <div class="notification-message">
                                    {{ Str::limit($data['message'], 100) }}
                                </div>
                            @endif
                            <div class="notification-time mt-2">
                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="notification-actions">
                            @if($isUnread)
                                <span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">New</span>
                            @endif
                        </div>
                    </div>
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
        <div class="notifications-list" id="notifications-list-container" data-latest-id=""></div>
        <div class="empty-state" id="notifications-empty-state" style="display:block">
            <i class="bi bi-bell-slash empty-state-icon"></i>
            <h4 class="fw-bold mb-2">No notifications</h4>
            <p class="text-muted mb-0">You're all caught up! We'll notify you when there's something new.</p>
        </div>
    @endif
</div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="notificationModalBody">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewNotification(notificationId, event) {
        // Don't trigger if clicking delete button
        if (event.target.closest('.notification-delete-btn')) {
            return;
        }

        // Mark as read
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                const badge = item.querySelector('.badge');
                if (badge) badge.remove();
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.style.display = 'none';
            }
            updateNotificationCount();
        });

        // Get notification data (from initial load or from data attribute for polled notifications)
        const notificationItem = event.currentTarget;
        const notificationData = @json($notificationDataForJs ?? []);
        let data = notificationData[notificationId];
        if (!data && notificationItem) {
            const attr = notificationItem.getAttribute('data-notification-data');
            if (attr) try { data = JSON.parse(attr); } catch (e) {}
        }
        if (!data) return;

        const type = data.type || '';
        
        // Build modal content
        showNotificationDetailModal(notificationId, type, data);
    }

    function showNotificationDetailModal(notificationId, type, data) {
        const modalBody = document.getElementById('notificationModalBody');
        
        // Get icon and color
        const icons = {
            'order_status': 'bi-box-seam',
            'trade_offer_received': 'bi-arrow-left-right',
            'trade_offer_accepted': 'bi-check-circle',
            'trade_status_updated': 'bi-arrow-repeat',
            'trade_listing_submitted': 'bi-hourglass-split',
            'trade_listing_approved': 'bi-check-circle',
            'trade_listing_rejected': 'bi-x-circle',
            'seller_approved': 'bi-shield-check',
            'seller_rejected': 'bi-x-circle',
            'seller_suspended': 'bi-exclamation-triangle',
            'account_banned': 'bi-ban',
            'account_suspended': 'bi-pause-circle',
            'profile_update': 'bi-person-check',
            'auction_won': 'bi-trophy',
            'auction_outbid': 'bi-hammer',
            'order_shipped': 'bi-truck',
            'order_delivered': 'bi-check-circle',
            'payment_success': 'bi-credit-card-2-front',
            'product_approved': 'bi-check-circle',
            'product_rejected': 'bi-x-circle',
            'document_rejected': 'bi-file-earmark-x',
            'business_page_revision_approved': 'bi-check-circle',
            'business_page_revision_rejected': 'bi-x-circle',
        };
        
        const colors = {
            'order_status': 'primary',
            'trade_offer_received': 'info',
            'trade_offer_rejected': 'danger',
            'trade_offer_accepted': 'success',
            'trade_status_updated': 'info',
            'trade_listing_submitted': 'warning',
            'trade_listing_approved': 'success',
            'trade_listing_rejected': 'danger',
            'seller_approved': 'success',
            'seller_rejected': 'danger',
            'seller_suspended': 'warning',
            'account_banned': 'danger',
            'account_suspended': 'warning',
            'profile_update': 'primary',
            'auction_won': 'success',
            'auction_outbid': 'warning',
            'order_shipped': 'info',
            'order_delivered': 'success',
            'payment_success': 'success',
            'product_approved': 'success',
            'product_rejected': 'danger',
            'document_rejected': 'danger',
            'business_page_revision_approved': 'success',
            'business_page_revision_rejected': 'danger',
        };

        const boxColors = {
            'seller_rejected': 'danger',
            'seller_suspended': 'warning',
            'seller_approved': 'success',
            'trade_listing_rejected': 'danger',
            'trade_listing_approved': 'success',
            'product_approved': 'success',
            'product_rejected': 'danger',
            'document_rejected': 'danger',
            'business_page_revision_rejected': 'danger',
            'business_page_revision_approved': 'success',
            'account_banned': 'danger',
            'account_suspended': 'warning',
        };
        
        const icon = icons[type] || 'bi-bell';
        const color = colors[type] || 'secondary';
        const boxColor = boxColors[type] || 'info';
        
        let content = `
            <div class="notification-detail-view">
                <div class="notification-detail-header">
                    <div class="notification-detail-icon bg-${color} text-white">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div class="notification-detail-header-content">
                        <h3 class="notification-detail-title">${data.title || 'Notification'}</h3>
                        <div class="notification-detail-meta">
                            <span><i class="bi bi-clock me-1"></i>${data.created_at || 'Just now'}</span>
                            ${data.business_name ? `<span><i class="bi bi-shop me-1"></i>${data.business_name}</span>` : ''}
                        </div>
                    </div>
                </div>
                <div class="notification-detail-body">`;

        // Add message
        if (data.message) {
            content += `
                <div class="notification-detail-section">
                    <div class="notification-detail-box ${boxColor}">
                        <div class="notification-detail-text">${data.message}</div>
                    </div>
                </div>`;
        }

        // Add reason if exists
        if (data.reason) {
            content += `
                <div class="notification-detail-section">
                    <div class="notification-detail-section-title">
                        <i class="bi bi-file-text-fill text-${color}"></i>
                        Detailed Reason
                    </div>
                    <div class="notification-detail-box ${boxColor}">
                        <div class="notification-detail-text">${data.reason}</div>
                    </div>
                </div>`;
        }

        // Add additional info grid
        let hasAdditionalInfo = false;
        let additionalInfoHtml = '<div class="notification-info-grid">';
        
        if (data.order_id) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Order ID</div>
                    <div class="notification-info-value">#${data.order_id}</div>
                </div>`;
        }
        
        if (data.order_number) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Order Number</div>
                    <div class="notification-info-value">${data.order_number}</div>
                </div>`;
        }
        
        if (data.tracking_number) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Tracking Number</div>
                    <div class="notification-info-value">${data.tracking_number}</div>
                </div>`;
        }
        
        if (data.report_id) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Report ID</div>
                    <div class="notification-info-value">#${data.report_id}</div>
                </div>`;
        }

        if (data.listing_id) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Listing ID</div>
                    <div class="notification-info-value">#${data.listing_id}</div>
                </div>`;
        }

        if (data.product_id) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Product ID</div>
                    <div class="notification-info-value">#${data.product_id}</div>
                </div>`;
        }

        if (data.auction_id) {
            hasAdditionalInfo = true;
            additionalInfoHtml += `
                <div class="notification-info-item">
                    <div class="notification-info-label">Auction ID</div>
                    <div class="notification-info-value">#${data.auction_id}</div>
                </div>`;
        }
        
        additionalInfoHtml += '</div>';
        
        if (hasAdditionalInfo) {
            content += `
                <div class="notification-detail-section">
                    <div class="notification-detail-section-title">
                        <i class="bi bi-info-circle-fill text-${color}"></i>
                        Additional Information
                    </div>
                    ${additionalInfoHtml}
                </div>`;
        }

        content += `</div>`;

        // Add action buttons
        if (data.action_url) {
            content += `
                <div class="notification-detail-actions">
                    <a href="${data.action_url}" class="notification-detail-btn notification-detail-btn-primary">
                        <i class="bi bi-arrow-right-circle"></i>
                        View Details
                    </a>
                    <button type="button" class="notification-detail-btn notification-detail-btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Close
                    </button>
                </div>`;
        }

        content += `</div>`;
        
        modalBody.innerHTML = content;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
        modal.show();
    }
    
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

    // Real-time: poll for new notifications every 3 seconds
    var _pageLoadTime = new Date().toISOString();
    (function pollNotifications() {
        var container = document.getElementById('notifications-list-container');
        if (!container) return;
        
        function doPoll() {
            var afterId = container.getAttribute('data-latest-id') || '';
            var url = afterId
                ? '{{ route("notifications.poll") }}?after_id=' + encodeURIComponent(afterId)
                : '{{ route("notifications.poll") }}?since=' + encodeURIComponent(_pageLoadTime);
            
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.notifications && res.notifications.length > 0) {
                        var emptyEl = document.getElementById('notifications-empty-state');
                        if (emptyEl) emptyEl.style.display = 'none';
                        res.notifications.forEach(function(n) {
                            var html = buildNotificationItemHtml(n);
                            container.insertAdjacentHTML('afterbegin', html);
                        });
                        var newestId = res.notifications[res.notifications.length - 1].id;
                        container.setAttribute('data-latest-id', newestId);
                        if (typeof updateNotificationCount === 'function') updateNotificationCount();
                        var badge = document.querySelector('.notification-badge');
                        if (badge && res.total_count !== undefined) {
                            badge.textContent = res.total_count > 99 ? '99+' : res.total_count;
                            badge.style.display = 'block';
                        }
                    }
                })
                .catch(function() {});
            setTimeout(doPoll, 2000);
        }
        setTimeout(doPoll, 1000);
    })();
    
    function buildNotificationItemHtml(n) {
        var data = n.data || {};
        var type = data.type || 'default';
        var icons = { order_status: 'bi-box-seam', trade_offer_received: 'bi-arrow-left-right', trade_offer_rejected: 'bi-x-circle', trade_offer_accepted: 'bi-check-circle', trade_status_updated: 'bi-arrow-repeat', trade_listing_submitted: 'bi-hourglass-split', trade_listing_approved: 'bi-check-circle', trade_listing_rejected: 'bi-x-circle', seller_approved: 'bi-shield-check', seller_rejected: 'bi-x-circle', seller_suspended: 'bi-exclamation-triangle', account_banned: 'bi-ban', account_suspended: 'bi-pause-circle', profile_update: 'bi-person-check', auction_won: 'bi-trophy', auction_outbid: 'bi-hammer', order_shipped: 'bi-truck', order_delivered: 'bi-check-circle', payment_success: 'bi-credit-card-2-front', product_approved: 'bi-check-circle', product_rejected: 'bi-x-circle', document_rejected: 'bi-file-earmark-x', business_page_revision_approved: 'bi-check-circle', business_page_revision_rejected: 'bi-x-circle' };
        var colors = { order_status: 'primary', trade_offer_received: 'info', trade_offer_rejected: 'danger', trade_offer_accepted: 'success', trade_status_updated: 'info', trade_listing_submitted: 'warning', trade_listing_approved: 'success', trade_listing_rejected: 'danger', seller_approved: 'success', seller_rejected: 'danger', seller_suspended: 'warning', account_banned: 'danger', account_suspended: 'warning', profile_update: 'primary', auction_won: 'success', auction_outbid: 'warning', order_shipped: 'info', order_delivered: 'success', payment_success: 'success', product_approved: 'success', product_rejected: 'danger', document_rejected: 'danger', business_page_revision_approved: 'success', business_page_revision_rejected: 'danger' };
        var icon = icons[type] || 'bi-bell';
        var color = colors[type] || 'secondary';
        var title = data.title || data.message || 'New notification';
        var msg = (data.message && data.title && data.message !== data.title) ? '<div class="notification-message">' + (data.message.length > 100 ? data.message.substring(0, 100) + '...' : data.message) + '</div>' : '';
        var business = (data.business_name) ? '<div class="notification-message text-muted"><i class="bi bi-shop me-1"></i>' + data.business_name + '</div>' : '';
        var dataAttr = (n.data ? ' data-notification-data="' + JSON.stringify(n.data).replace(/"/g, '&quot;') + '"' : '');
        return '<div class="notification-item unread" data-notification-id="' + n.id + '"' + dataAttr + ' onclick="viewNotification(\'' + n.id + '\', event)"><span class="notification-dot"></span><div class="notification-item-content"><div class="notification-icon bg-' + color + ' text-white"><i class="bi ' + icon + '"></i></div><div class="notification-content"><div class="notification-title fw-bold">' + (title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div>' + business + msg + '<div class="notification-time mt-2"><i class="bi bi-clock me-1"></i>' + (n.created_at || 'Just now') + '</div></div><div class="notification-actions"><span class="badge bg-primary rounded-pill" style="font-size: 0.7rem;">New</span></div></div><button type="button" class="notification-delete-btn" onclick="deleteNotification(\'' + n.id + '\', event)" title="Delete notification"><i class="bi bi-trash"></i></button></div>';
    }

    // Auto-open notification modal if 'open' parameter is present in URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const notificationIdToOpen = urlParams.get('open');
        
        if (notificationIdToOpen) {
            // Find the notification item
            const notificationItem = document.querySelector(`[data-notification-id="${notificationIdToOpen}"]`);
            if (notificationItem) {
                // Scroll to the notification
                notificationItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Highlight the notification briefly
                notificationItem.style.transition = 'all 0.3s ease';
                notificationItem.style.boxShadow = '0 0 0 3px rgba(14, 165, 233, 0.3)';
                
                // Trigger click to open modal after a short delay
                setTimeout(() => {
                    notificationItem.click();
                    // Remove highlight
                    setTimeout(() => {
                        notificationItem.style.boxShadow = '';
                    }, 500);
                }, 300);
                
                // Clean URL without reloading
                const cleanUrl = window.location.pathname + (urlParams.toString().replace(/&?open=[^&]*/, '').replace(/^\?$/, '') ? '?' + urlParams.toString().replace(/&?open=[^&]*/, '').replace(/^&/, '') : '');
                window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
            }
        }
    });
</script>
@endpush
@endsection
