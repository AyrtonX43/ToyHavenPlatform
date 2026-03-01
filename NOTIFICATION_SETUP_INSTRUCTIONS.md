# Email Notification Setup Instructions

## Issue
After approving a seller, the notification is not being sent via email on the live server.

## Solution

### Step 1: Run Database Migration (On Live Server via PuTTY)

```bash
cd /path/to/ToyHavenPlatform
php artisan migrate
```

This will create the `notifications` table needed for in-app notifications.

### Step 2: Configure Email Settings in .env (On Live Server)

Edit your `.env` file on the live server and update these settings:

#### Option A: Using Gmail (Recommended for Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="ToyHaven Platform"
```

**Important for Gmail:**
- You need to use an "App Password", not your regular Gmail password
- Go to: https://myaccount.google.com/apppasswords
- Generate an app password and use that in MAIL_PASSWORD

#### Option B: Using Mailtrap (For Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@toyhaven.com
MAIL_FROM_NAME="ToyHaven Platform"
```

#### Option C: Using SendGrid (For Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@toyhaven.com
MAIL_FROM_NAME="ToyHaven Platform"
```

### Step 3: Clear Config Cache (On Live Server)

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test Email Sending

You can test if email is working by running this command on the live server:

```bash
php artisan tinker
```

Then in tinker:

```php
Mail::raw('Test email from ToyHaven', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

Press `Ctrl+C` to exit tinker.

Check if you received the test email.

### Step 5: Enable Database Notifications (Optional)

After running the migration, you can enable database notifications by uncommenting this line in `SellerApprovedNotification.php`:

Change from:
```php
return ['mail'];
```

To:
```php
return ['mail', 'database'];
```

This will show notifications in the user's notification bell icon.

## Troubleshooting

### Email Not Sending

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check if MAIL_MAILER is set:**
   ```bash
   php artisan config:show mail.default
   ```

3. **Verify .env is loaded:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Common Issues

1. **"Connection refused"** - Check MAIL_HOST and MAIL_PORT
2. **"Authentication failed"** - Check MAIL_USERNAME and MAIL_PASSWORD
3. **"No route to host"** - Check firewall settings on server
4. **"SSL/TLS error"** - Try changing MAIL_ENCRYPTION from 'tls' to 'ssl' or vice versa

## Quick Setup for Production

For production, I recommend using **SendGrid** (free tier: 100 emails/day):

1. Sign up at https://sendgrid.com
2. Create an API Key
3. Update .env with SendGrid settings
4. Run `php artisan config:clear`
5. Test the approval again

## Current Status

- ✅ Notification code is working
- ✅ Error handling is in place
- ✅ Migration file created
- ⚠️ Email configuration needs to be set up on live server
- ⚠️ Database migration needs to be run on live server

## Files Modified

- `app/Notifications/SellerApprovedNotification.php` - Notification class
- `app/Http/Controllers/Admin/SellerController.php` - Approval logic with error handling
- `database/migrations/2026_03_02_035246_create_notifications_table.php` - Notifications table
