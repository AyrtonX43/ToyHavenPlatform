# Check Email Configuration

## Issue
Seller approval notifications are not being sent via email.

## Steps to Debug on Live Server

### 1. Check if mail is configured in `.env` file:
```bash
cd /path/to/ToyHavenPlatform
cat .env | grep MAIL
```

You should see something like:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="ToyHaven Platform"
```

### 2. Check Laravel logs for errors:
```bash
tail -f storage/logs/laravel.log
```

Look for lines containing:
- "Attempting to send approval notification"
- "Approval notification sent successfully"
- "Failed to send seller approval notification"

### 3. Test email sending manually:
```bash
php artisan tinker
```

Then in tinker:
```php
$user = \App\Models\User::first();
$user->notify(new \App\Notifications\SellerApprovedNotification('Test Business', 'Local Business Toyshop'));
exit
```

### 4. If mail is not configured, you have two options:

#### Option A: Use Log Driver (for testing)
In `.env`:
```
MAIL_MAILER=log
```
Emails will be written to `storage/logs/laravel.log` instead of being sent.

#### Option B: Configure SMTP (for production)
Use Gmail, SendGrid, Mailgun, or your hosting provider's SMTP settings.

For Gmail:
1. Enable 2-factor authentication
2. Generate an App Password
3. Use that in `MAIL_PASSWORD`

### 5. After configuring, clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### 6. Try approving a seller again

## What Changed in Latest Update

1. **Removed Queue**: Notifications now send immediately instead of being queued
2. **Added Logging**: Check `storage/logs/laravel.log` for detailed info
3. **Simplified**: Using mail-only (no database notifications) for now
4. **Error Handling**: Approval succeeds even if email fails

## Commit
- **56fca19**: Improve notification reliability and add logging
