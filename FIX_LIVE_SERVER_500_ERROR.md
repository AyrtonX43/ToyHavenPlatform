# Fix 500 Server Error After Payment Success (Live Server)

## 🔴 Problem
Getting "500 | Server Error" after successful QR Ph payment on live server (toyhaven.online).

## 🔍 Common Causes

The 500 error after payment typically happens due to:

1. **Missing DomPDF package** - Receipt generation fails
2. **Storage permissions** - Can't write receipt files
3. **Missing directories** - `storage/app/public/receipts/` doesn't exist
4. **Email configuration** - Notification sending fails
5. **Memory limit** - PDF generation exceeds PHP memory
6. **Missing dependencies** - Composer packages not installed

---

## ✅ Solution - Run These Commands on Your Live Server

### Step 1: Connect to Your Server via PuTTY
```bash
ssh u334258035@toyhaven.online
# Or use your actual SSH credentials
```

### Step 2: Navigate to Your Project
```bash
cd /home/u334258035/domains/toyhaven.online
# Or wherever your Laravel project is located
```

### Step 3: Pull Latest Code
```bash
git pull origin main
```

### Step 4: Install Dependencies
```bash
# Install all required packages including DomPDF
composer install --no-dev --optimize-autoloader

# If composer install fails, try:
composer update --no-dev
```

### Step 5: Fix Permissions
```bash
# Make storage writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If you're using Apache/Nginx, set proper ownership
chown -R www-data:www-data storage bootstrap/cache

# Or if using a different user (check with: ps aux | grep apache)
# chown -R your-web-user:your-web-user storage bootstrap/cache
```

### Step 6: Create Receipt Directory
```bash
# Create receipts directory if it doesn't exist
mkdir -p storage/app/public/receipts
chmod -R 775 storage/app/public/receipts

# Create storage link
php artisan storage:link
```

### Step 7: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
```

### Step 8: Update Live `.env` File
```bash
nano .env
```

Make sure these are set correctly:
```env
# PayMongo LIVE mode
PAYMONGO_MODE=live
PAYMONGO_SECRET_KEY=sk_live_YOUR_ACTUAL_LIVE_KEY
PAYMONGO_PUBLIC_KEY=pk_live_YOUR_ACTUAL_LIVE_KEY

# Make sure APP_DEBUG is false in production
APP_DEBUG=false
APP_ENV=production

# Check mail configuration is correct
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@example.com"
MAIL_FROM_NAME="ToyHaven Platform"
```

### Step 9: Check PHP Memory Limit
```bash
# Check current memory limit
php -i | grep memory_limit

# If it's too low (< 256M), edit php.ini:
# Find: memory_limit = 128M
# Change to: memory_limit = 512M
```

### Step 10: Restart Web Server (if needed)
```bash
# For Apache:
sudo systemctl restart apache2

# For Nginx + PHP-FPM:
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm  # adjust version as needed
```

---

## 🔍 Debugging - Check Server Logs

### Laravel Logs
```bash
tail -100 storage/logs/laravel.log
```

Look for errors related to:
- `Receipt generation failed`
- `Class "Barryvdh\DomPDF\Facade\Pdf" not found`
- `Permission denied`
- `Failed to create directory`
- `Notification sending failed`

### Apache/Nginx Error Logs
```bash
# Apache
tail -50 /var/log/apache2/error.log

# Nginx
tail -50 /var/log/nginx/error.log
```

### PHP Error Logs
```bash
tail -50 /var/log/php8.2-fpm.log  # adjust version
```

---

## 🧪 Test After Fixing

1. Create a new order (≥ ₱20)
2. Complete payment with real QR Ph
3. Check if:
   - ✅ Payment succeeds
   - ✅ No 500 error
   - ✅ Redirected to order page
   - ✅ Receipt is generated
   - ✅ Email is sent

---

## 🚨 Quick Fix If You Can't Access Server Right Now

If you need payments to work immediately but can't access the server, the code is already resilient:

**Current behavior:**
- ✅ Payment will succeed
- ✅ Order will be marked as paid
- ✅ User will be redirected
- ⚠️ Receipt generation will fail silently
- ⚠️ Email might fail if receipt can't be attached

The 500 error should NOT happen with the latest code because we wrapped everything in try-catch blocks. If you're still getting 500, it means:
1. You haven't pulled the latest code on live server yet
2. Or there's a different error

---

## 📋 Checklist for Live Server

Run these on your live server via PuTTY:

- [ ] `git pull origin main`
- [ ] `composer install --no-dev`
- [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] `mkdir -p storage/app/public/receipts`
- [ ] `php artisan storage:link`
- [ ] `php artisan config:cache`
- [ ] Update `.env` with live keys
- [ ] Test payment with real money (≥ ₱20)

---

## 💡 Most Likely Cause

Based on the previous `FIX_500_ERROR.md`, the issue is:
1. **DomPDF package not installed** on live server
2. **Old code without try-catch** still running on live server

**Solution:** Pull the latest code and run `composer install` on your live server!

---

## Need Help?

If the error persists after following these steps, please:
1. Share the error from your live server's `storage/logs/laravel.log`
2. Let me know what step failed
3. Share any error messages from the terminal

I'll help you fix it!
