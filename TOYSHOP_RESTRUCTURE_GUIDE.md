# ToyShop Restructure - Complete Implementation Guide

## Overview
This document outlines the complete restructuring of the ToyHaven ToyShop system with a comprehensive order management, receipt confirmation, dispute resolution, and moderator system.

---

## 🎯 Process Flow

### 1. **Product Browsing & Cart**
- User browses products
- Adds products to wishlist or cart
- Selects items for checkout

### 2. **Checkout Process**
```
View Cart → Select Items → Checkout Page → Review Order Details
→ Enter Shipping Info → Proceed to Payment
```

### 3. **Payment Flow (QRPH/Card via PayMongo)**

#### **Payment Success:**
```
Payment Confirmed
→ Order status: payment_status = 'paid'
→ Generate PDF receipt
→ Send receipt to user email
→ Notify user (email + in-app notification)
→ Notify seller (email + in-app notification)
→ User redirected to order details page
```

#### **Payment Failed:**
```
Payment Failed/Declined
→ Order remains: payment_status = 'pending'
→ Product stock restored
→ User can retry payment from "My Orders"
→ OR user can cancel order
```

#### **Payment Abandoned:**
```
User closes browser without completing payment
→ Order remains: payment_status = 'pending'
→ User can return to retry payment
→ Auto-cancel after 24 hours (scheduled job)
→ Stock automatically restored on auto-cancel
```

### 4. **Order Fulfillment (Seller/Moderator)**

#### **Order Status Flow:**
```
pending → processing → packed → shipped → in_transit 
→ out_for_delivery → delivered
```

#### **Seller Actions:**
1. Receives notification of paid order
2. Updates status to "processing"
3. Packs items → Updates to "packed"
4. Ships order:
   - Enters tracking number
   - Selects courier (J&T, LBC, etc.)
   - Sets estimated delivery date
   - Updates to "shipped"
5. Each status update:
   - Sends email to customer
   - Sends in-app notification
   - Creates tracking entry

### 5. **Delivery & Receipt Confirmation**

#### **Delivery Notification:**
```
Courier/Seller marks as "delivered"
→ Order: status = 'delivered', delivered_at = now()
→ Email sent to user: "Order Delivered - Please Confirm Receipt"
→ In-app notification with action required badge
→ User has 3 days to confirm receipt
```

#### **Receipt Confirmation (MANDATORY):**
```
User must:
1. Go to order details page
2. Click "Confirm Receipt"
3. Upload proof photo (mandatory)
4. Add optional delivery notes
5. Submit confirmation

System:
→ Creates OrderReceipt record
→ Saves photo to storage/order-receipts/
→ Updates order: receipt_confirmed_at = now()
→ Notifies seller: "Customer confirmed receipt"
→ Enables "Review Product" button
→ Product moves to "Previously Ordered Items"
```

#### **Non-Receipt Dispute:**
```
IF user doesn't receive product BUT status shows "delivered":

User clicks "Report Issue" →
→ Select reason: not_received, damaged, wrong_item, incomplete, other
→ Write description (min 20 chars)
→ Upload evidence photos (optional)
→ Submit dispute

System:
→ Creates OrderDispute record
→ Generates dispute number (DSP-YYYYMMDD-XXXXXX)
→ Updates order: has_dispute = true
→ Notifies seller (email + in-app)
→ Notifies all moderators (email + in-app)
→ Opens dispute chat thread
```

### 6. **Dispute Resolution (Moderator)**

#### **Dispute Management:**
```
Moderator receives notification →
→ Views dispute details in moderator dashboard
→ Reviews evidence (photos, tracking info)
→ Communicates with user & seller via dispute chat
→ Investigates case
→ Updates status: open → investigating → resolved

Resolution Options:
1. Refund: Full refund issued, payment_status = 'refunded'
2. Replacement: New order created for replacement
3. Partial Refund: Partial amount refunded
4. No Action: Dispute closed without action

After resolution:
→ Dispute: status = 'resolved', resolved_at = now()
→ Notify user & seller of resolution
→ Close dispute chat
```

### 7. **Order Cancellation**

#### **User Cancellation:**
```
User can cancel IF:
- payment_status = 'pending' (not paid yet)
- status = 'pending' or 'processing' (not shipped)

Cancellation flow:
→ User provides cancellation reason
→ Order: status = 'cancelled', cancelled_at = now()
→ Restore product stock
→ IF paid: process refund (payment_status = 'refunded')
→ Notify seller
```

#### **Auto-Cancellation (Scheduled Job):**
```
Runs hourly:
→ Finds orders: payment_status = 'pending' AND created > 24h ago
→ Updates: status = 'cancelled'
→ Restores stock
→ Logs cancellation
→ Notifies seller
```

### 8. **Product Review**
```
After receipt confirmation:
→ User can review product
→ Rating (1-5 stars) + comment
→ Review appears on product page
→ Product marked as "Previously Ordered"
```

---

## 📁 Files Created/Modified

### **Migrations:**
1. `2026_03_01_000001_add_moderator_role_to_users_table.php`
2. `2026_03_01_000002_create_order_receipts_table.php`
3. `2026_03_01_000003_create_order_disputes_table.php`
4. `2026_03_01_000004_add_receipt_and_dispute_fields_to_orders_table.php`
5. `2026_03_01_000005_create_order_dispute_messages_table.php`
6. `2026_03_01_000006_add_moderator_permissions_to_users_table.php`

### **Models:**
1. `app/Models/OrderReceipt.php` - Receipt confirmation with photo
2. `app/Models/OrderDispute.php` - Dispute management
3. `app/Models/OrderDisputeMessage.php` - Dispute chat messages
4. `app/Models/Order.php` - Updated with new relationships & methods
5. `app/Models/User.php` - Added moderator methods & relationships

### **Controllers:**
1. `app/Http/Controllers/Toyshop/OrderReceiptController.php`
2. `app/Http/Controllers/Toyshop/OrderDisputeController.php`
3. `app/Http/Controllers/Moderator/OrderController.php`
4. `app/Http/Controllers/Moderator/DisputeController.php`
5. `app/Http/Controllers/Admin/ModeratorController.php`
6. Updated: `Toyshop/OrderController.php` - Added cancel & retry payment
7. Updated: `Seller/OrderController.php` - Added notifications
8. Updated: `Toyshop/CheckoutController.php` - Enhanced payment flow

### **Notifications:**
1. `OrderStatusUpdatedNotification.php`
2. `OrderDeliveredNotification.php`
3. `OrderReceiptConfirmedNotification.php`
4. `DisputeOpenedNotification.php`
5. `DisputeMessageNotification.php`
6. `DisputeResolvedNotification.php`
7. `OrderCancelledNotification.php`

### **Jobs:**
1. `GenerateOrderReceiptPDF.php` - Generates & emails PDF receipt
2. `AutoCancelPendingOrders.php` - Auto-cancels unpaid orders after 24h

### **Middleware:**
1. `EnsureModeratorRole.php` - Protects moderator routes

### **Views:**
1. `resources/views/emails/order-receipt.blade.php`
2. `resources/views/pdf/order-receipt.blade.php`

---

## 🔧 Installation Steps

### 1. **Run Migrations:**
```bash
php artisan migrate
```

### 2. **Create Storage Link (if not exists):**
```bash
php artisan storage:link
```

### 3. **Install DomPDF (for PDF generation):**
```bash
composer require barryvdh/laravel-dompdf
```

### 4. **Configure Queue Worker:**
```bash
# For development:
php artisan queue:work

# For production (use supervisor):
php artisan queue:work --daemon
```

### 5. **Configure Scheduler:**
Add to crontab (Linux/Mac) or Task Scheduler (Windows):
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. **Create First Moderator:**
```bash
php artisan tinker
```
```php
$moderator = User::create([
    'name' => 'Moderator Name',
    'email' => 'moderator@toyhaven.com',
    'password' => Hash::make('password123'),
    'role' => 'moderator',
    'moderator_permissions' => ['manage_orders', 'manage_disputes'],
    'moderator_assigned_at' => now(),
    'assigned_by' => 1, // Admin user ID
    'email_verified_at' => now(),
]);
```

---

## 🎨 Moderator Permissions

Available permissions:
- `manage_orders` - Update order status, view all orders
- `manage_disputes` - Handle disputes, chat with users/sellers
- `view_reports` - View system reports
- `manage_sellers` - Manage seller accounts

---

## 🔗 Routes

### **Customer Routes:**
- `GET /orders` - View all orders
- `GET /orders/{id}` - View order details
- `POST /orders/{id}/cancel` - Cancel order
- `POST /orders/{id}/receipt` - Confirm receipt with photo
- `GET /orders/disputes/{orderId}/create` - Create dispute
- `POST /orders/disputes/{orderId}` - Submit dispute
- `GET /orders/disputes/{disputeId}` - View dispute details
- `POST /orders/disputes/{disputeId}/message` - Send dispute message

### **Seller Routes:**
- `GET /seller/orders` - View seller orders
- `POST /seller/orders/{id}/status` - Update order status

### **Moderator Routes:**
- `GET /moderator/orders` - View all orders
- `GET /moderator/orders/{id}` - View order details
- `POST /moderator/orders/{id}/status` - Update order status
- `GET /moderator/disputes` - View all disputes
- `GET /moderator/disputes/{id}` - View dispute details
- `POST /moderator/disputes/{id}/resolve` - Resolve dispute
- `POST /moderator/disputes/{id}/message` - Send message in dispute

### **Admin Routes:**
- `GET /admin/moderators` - List moderators
- `POST /admin/moderators` - Create moderator
- `PUT /admin/moderators/{id}` - Update moderator
- `DELETE /admin/moderators/{id}` - Remove moderator role

---

## 📊 Database Schema

### **orders table (additions):**
- `receipt_confirmed_at` - Timestamp when user confirmed receipt
- `has_dispute` - Boolean flag for active dispute
- `courier_name` - Name of delivery courier
- `cancelled_at` - Timestamp of cancellation
- `cancellation_reason` - Reason for cancellation
- `cancelled_by` - User ID who cancelled

### **order_receipts table:**
- `id`
- `order_id`
- `receipt_number` - Unique receipt number
- `proof_photo_path` - Path to uploaded photo
- `delivery_notes` - Optional notes from user
- `confirmed_at` - Confirmation timestamp

### **order_disputes table:**
- `id`
- `order_id`
- `user_id`
- `seller_id`
- `moderator_id` - Assigned moderator
- `dispute_number` - Unique dispute number
- `reason` - not_received, damaged, wrong_item, incomplete, other
- `description` - Detailed description
- `evidence_photos` - JSON array of photo paths
- `status` - open, investigating, resolved, closed
- `resolution` - refund, replacement, partial_refund, no_action
- `resolution_notes` - Moderator notes
- `resolved_at` - Resolution timestamp

### **order_dispute_messages table:**
- `id`
- `order_dispute_id`
- `user_id` - Sender
- `message` - Message content
- `attachments` - JSON array of file paths
- `is_internal` - Boolean for moderator-only messages

### **users table (additions):**
- `moderator_permissions` - JSON array of permissions
- `moderator_assigned_at` - When moderator role was assigned
- `assigned_by` - Admin who assigned moderator role

---

## 🚀 Testing Checklist

### **Order Flow:**
- [ ] Place order with QRPH payment
- [ ] Place order with card payment
- [ ] Verify receipt email sent
- [ ] Verify PDF receipt attached
- [ ] Verify seller notification

### **Payment Scenarios:**
- [ ] Successful payment
- [ ] Failed payment
- [ ] Abandoned payment (check auto-cancel after 24h)
- [ ] Retry payment from My Orders

### **Order Management:**
- [ ] Seller updates order status
- [ ] User receives status update notifications
- [ ] Tracking number saved correctly
- [ ] Delivery confirmation sent

### **Receipt Confirmation:**
- [ ] User can upload receipt photo
- [ ] Photo saved to storage
- [ ] Seller receives confirmation notification
- [ ] Review button enabled after confirmation

### **Dispute System:**
- [ ] User can open dispute
- [ ] Seller receives dispute notification
- [ ] Moderator receives dispute notification
- [ ] Chat messages work correctly
- [ ] Moderator can resolve dispute
- [ ] Refund processed correctly

### **Moderator System:**
- [ ] Admin can create moderator
- [ ] Moderator can access dashboard
- [ ] Moderator can view all orders
- [ ] Moderator can manage disputes
- [ ] Permissions work correctly

### **Cancellation:**
- [ ] User can cancel unpaid order
- [ ] Stock restored on cancellation
- [ ] Refund processed for paid orders
- [ ] Auto-cancel job runs correctly

---

## 🐛 Common Issues & Solutions

### **Issue: PDF not generating**
**Solution:** Install DomPDF:
```bash
composer require barryvdh/laravel-dompdf
```

### **Issue: Queue jobs not running**
**Solution:** Start queue worker:
```bash
php artisan queue:work
```

### **Issue: Scheduled jobs not running**
**Solution:** Configure cron/task scheduler:
```bash
* * * * * cd /path-to-project && php artisan schedule:run
```

### **Issue: Images not uploading**
**Solution:** Create storage link:
```bash
php artisan storage:link
chmod -R 775 storage
```

### **Issue: Emails not sending**
**Solution:** Check `.env` mail configuration:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@toyhaven.com
```

---

## 📝 Notes

1. **Receipt Photo Requirement:** Users MUST upload a photo to confirm receipt. This prevents false "not received" claims.

2. **Dispute Timeline:** Users have 3 days after delivery to confirm receipt or open a dispute.

3. **Auto-Cancel:** Unpaid orders are automatically cancelled after 24 hours to free up inventory.

4. **Moderator Assignment:** Disputes are automatically assigned to the first moderator who views them.

5. **Stock Management:** Stock is decremented on order creation and restored on cancellation.

6. **Refund Processing:** Refunds are marked in the system but must be processed manually through PayMongo dashboard.

---

## 🎉 Success!

Your ToyShop system is now fully restructured with:
✅ Complete order lifecycle management
✅ Receipt confirmation with photo proof
✅ Dispute resolution system
✅ Moderator dashboard & access control
✅ Automated email & PDF receipts
✅ Real-time notifications
✅ Stock management
✅ Payment retry functionality
✅ Auto-cancellation of abandoned orders

**Next Steps:**
1. Run migrations
2. Create test moderator account
3. Test complete order flow
4. Configure queue worker & scheduler
5. Customize email templates (optional)
6. Add frontend views (as needed)

---

**Created:** March 1, 2026
**Version:** 1.0.0
**Author:** ToyHaven Development Team
