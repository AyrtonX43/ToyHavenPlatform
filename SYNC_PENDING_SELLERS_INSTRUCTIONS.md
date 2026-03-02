# Sync Pending Seller Notifications

This document explains how to send application submitted notifications to existing pending sellers who submitted their applications before the notification feature was added.

## What This Does

The command will:
- Find all sellers with `verification_status = 'pending'`
- Check if they already received the application submitted notification
- Send email + in-app notification to those who haven't received it yet
- Skip sellers who already have the notification
- Provide a summary of results

## How to Run

### On Local Development (XAMPP)

```bash
# Navigate to your project directory
cd C:\xampp\htdocs\ToyHavenPlatform

# Run the command
php artisan seller:sync-pending-notifications
```

### On Live Server (via PuTTY)

```bash
# SSH into your server
ssh your-username@your-server

# Navigate to your project directory
cd /path/to/ToyHavenPlatform

# Run the command
php artisan seller:sync-pending-notifications
```

## What You'll See

The command will output:
```
Starting to sync pending seller notifications...
Found 5 pending seller(s).
✓ Sent notification to John Doe for business: John's Toy Store
✓ Sent notification to Jane Smith for business: Jane's Collectibles
Notification already exists for Mike's Toys (User: Mike Johnson). Skipping...
✓ Sent notification to Sarah Lee for business: Sarah's Action Figures
✗ Failed to send notification to Bob Brown: Email not configured

=== Summary ===
Total pending sellers: 5
Successfully sent: 3
Failed: 1
Skipped (already notified): 1

Sync completed!
```

## When to Run This

Run this command:
1. **After deploying the notification feature** - To notify existing pending sellers
2. **One time only** - The command checks for duplicates, so it's safe to run multiple times
3. **Before admin starts reviewing applications** - So all pending sellers get proper communication

## What Gets Sent

Each pending seller will receive:

### Email
- Subject: "Business Application Submitted - ToyHaven Platform"
- Confirmation of application submission
- Application type (Verified Trusted Toyshop or Local Business Toyshop)
- Review timeline (1-3 business days)
- What happens next
- Link to check status

### In-App Notification
- Title: "Business Application Submitted"
- Message: "Your business application for '[Business Name]' has been submitted and is pending admin approval."
- Link to Seller Dashboard

## Safety Features

✅ **No Duplicates**: Checks if notification already exists before sending
✅ **Error Handling**: Continues even if one notification fails
✅ **Detailed Logging**: Shows exactly what happened for each seller
✅ **Safe to Re-run**: Won't send duplicate notifications

## Troubleshooting

### "No pending sellers found"
- This means all sellers are either approved, rejected, or no sellers exist
- This is normal if you have no pending applications

### "Failed to send notification"
- Check email configuration in `.env`
- Verify SMTP settings are correct
- Check Laravel logs: `storage/logs/laravel.log`

### "Seller has no associated user"
- Database inconsistency - seller record exists but user was deleted
- You may need to manually clean up these records

## After Running

1. Check a few pending sellers' accounts to verify they received:
   - Email in their inbox
   - Notification in their notifications page

2. Check Laravel logs for any errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. If everything looks good, you're done! Future applications will automatically receive notifications.

## Database Query to Check

If you want to verify before running:

```sql
-- Count pending sellers
SELECT COUNT(*) as pending_count 
FROM sellers 
WHERE verification_status = 'pending';

-- See pending sellers details
SELECT 
    s.id,
    s.business_name,
    s.is_verified_shop,
    u.name as user_name,
    u.email,
    s.created_at
FROM sellers s
JOIN users u ON s.user_id = u.id
WHERE s.verification_status = 'pending'
ORDER BY s.created_at DESC;
```

## Notes

- This is a **one-time sync** command for existing data
- New seller registrations will automatically receive notifications
- The command is safe to run multiple times (won't create duplicates)
- Email sending may take a few seconds per seller
