# Admin Modal Layout & Notification Fix

## Overview
Fixed modal sizing issues and enhanced notification system for admin seller management.

## Issues Fixed

### 1. Modal Layout Issues
**Problem:** Modals were appearing too large, cut off at the top, and not properly centered.

**Root Causes:**
- Duplicate modals (one inside table rows, one outside)
- Using `modal-fullscreen` class causing viewport overflow
- Missing `modal-dialog-centered` class for proper vertical centering
- No scrollable content handling for long forms

**Solutions:**
- ✅ Removed duplicate modals from inside table rows
- ✅ Changed document viewer from `modal-fullscreen` to `modal-xl` with proper centering
- ✅ Added `modal-dialog-centered` to all modals for vertical centering
- ✅ Added `modal-dialog-scrollable` to forms with long content
- ✅ Set proper max-heights (80vh) for modal content
- ✅ Added responsive sizing with proper margins
- ✅ Enhanced modal headers with colored backgrounds and better icons

### 2. Notification System Enhancement
**Problem:** Admin actions (reject/suspend) only sent email notifications, not website notifications.

**Solution:**
- ✅ Updated all three notification classes to use both `mail` and `database` channels:
  - `SellerRejectedNotification`
  - `SellerSuspendedNotification`
  - `DocumentRejectedNotification`
- ✅ Enhanced `toArray()` methods with structured data for website notifications:
  - `type`: Notification type identifier
  - `title`: Short notification title
  - `message`: Brief notification message
  - `action_url`: Link to relevant page
  - `icon`: Bootstrap icon name
  - `color`: Badge/alert color
- ✅ Updated controller success messages to reflect dual notification delivery
- ✅ Updated modal UI text to inform admins about dual notifications

## Files Modified

### View Files
1. **resources/views/admin/sellers/show.blade.php**
   - Removed duplicate modals from table rows
   - Updated document viewer modal: `modal-fullscreen` → `modal-xl modal-dialog-centered`
   - Updated reject document modal: Added `modal-dialog-centered modal-dialog-scrollable`
   - Updated reject seller modal: Added `modal-dialog-centered modal-dialog-scrollable`
   - Updated suspend seller modal: Added `modal-dialog-centered modal-dialog-scrollable modal-lg`
   - Enhanced all modal headers with colored backgrounds
   - Added proper ARIA labels for accessibility
   - Improved modal body styling with better heights and overflow handling
   - Added CSS for modal positioning and responsive sizing
   - Updated notification text in modals to mention both email and website

### Notification Classes
2. **app/Notifications/SellerRejectedNotification.php**
   - Changed `via()` from `['mail']` to `['mail', 'database']`
   - Enhanced `toArray()` with structured notification data

3. **app/Notifications/SellerSuspendedNotification.php**
   - Changed `via()` from `['mail']` to `['mail', 'database']`
   - Enhanced `toArray()` with structured notification data

4. **app/Notifications/DocumentRejectedNotification.php**
   - Changed `via()` from `['mail']` to `['mail', 'database']`
   - Enhanced `toArray()` with structured notification data

### Controller
5. **app/Http/Controllers/Admin/SellerController.php**
   - Updated success messages in `reject()`, `suspend()`, and `rejectDocument()` methods
   - Messages now indicate "via email and website notification"

## Modal Specifications

### Document Viewer Modal
- **Size:** `modal-xl` (90% width, max 1140px on large screens)
- **Position:** Vertically and horizontally centered
- **Content Height:** 70vh for documents, 80vh max for modal body
- **Features:** Dark theme, download button, open in new tab, responsive image/PDF display

### Reject Document Modal
- **Size:** Default modal width
- **Position:** Vertically centered, scrollable content
- **Features:** Colored header (red), warning alerts, reason textarea

### Reject Seller Modal
- **Size:** Default modal width
- **Position:** Vertically centered, scrollable content
- **Features:** Colored header (red), dropdown reasons, feedback textarea

### Suspend Seller Modal
- **Size:** `modal-lg` (larger width for more content)
- **Position:** Vertically centered, scrollable content
- **Features:** Colored header (yellow), dropdown reasons, report linking, feedback textarea

## CSS Enhancements

Added responsive modal styling:
```css
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-dialog-scrollable {
    max-height: calc(100vh - 3.5rem);
}

.modal-xl {
    max-width: 90%;
    margin: 1.75rem auto;
}
```

## Notification Data Structure

Website notifications now include:
- `type`: e.g., "seller_rejected", "seller_suspended", "document_rejected"
- `title`: User-friendly title
- `message`: Brief description
- `reason`: Full reason/feedback from admin
- `action_url`: Link to relevant page
- `icon`: Bootstrap icon for display
- `color`: Badge color (danger, warning, etc.)

## Testing Checklist

1. **Modal Display:**
   - [ ] Open document viewer - should be centered, not fullscreen
   - [ ] Open reject document modal - should be centered, scrollable
   - [ ] Open reject seller modal - should be centered, scrollable
   - [ ] Open suspend seller modal - should be centered, larger width
   - [ ] Verify no modals are cut off at the top
   - [ ] Test on different screen sizes

2. **Notifications:**
   - [ ] Reject a seller - verify email sent AND database notification created
   - [ ] Suspend a seller - verify email sent AND database notification created
   - [ ] Reject a document - verify email sent AND database notification created
   - [ ] Check user's notification dropdown/page shows new notifications
   - [ ] Verify notification links work correctly

3. **Functionality:**
   - [ ] All forms still submit correctly
   - [ ] Rejection reasons populate correctly
   - [ ] Suspension reasons populate correctly
   - [ ] Document status updates correctly
   - [ ] User receives notifications in their account

## Notes

- The `Notifiable` trait on the User model automatically handles database notifications
- The `notifications` table migration already exists (2026_01_21_150410)
- No database migrations needed
- Email notifications remain unchanged (still working)
- Website notifications are now stored in the `notifications` table
- Users can view notifications through their notification center/dropdown
