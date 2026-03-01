# Quick Fix for Live Server 500 Error

## 🚨 The Problem
Getting "500 | Server Error" after QR Ph payment succeeds on toyhaven.online

## 🎯 The Solution (Copy & Paste These Commands)

### Open PuTTY and connect to your server, then run:

```bash
# 1. Go to your project directory
cd /home/u334258035/domains/toyhaven.online

# 2. Pull the latest code (this includes all the fixes)
git pull origin main

# 3. Install missing packages (especially DomPDF for receipts)
composer install --no-dev --optimize-autoloader

# 4. Create receipt directory
mkdir -p storage/app/public/receipts
chmod -R 775 storage/app/public/receipts

# 5. Fix all permissions
chmod -R 775 storage bootstrap/cache

# 6. Create storage link
php artisan storage:link

# 7. Clear and cache everything
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
```

### That's it! Test your payment again.

---

## 🔍 If Error Still Happens

Check the logs on your server:
```bash
tail -50 storage/logs/laravel.log
```

Send me the error message and I'll help you fix it.

---

## ✅ What This Fixes

- ✅ Installs DomPDF package (needed for receipt generation)
- ✅ Creates receipt directory with proper permissions
- ✅ Updates code with better error handling
- ✅ Clears all caches to use new code
- ✅ Fixes storage permissions issues

---

## 💰 After This Fix, When User Pays:

1. User scans QR code and pays
2. Payment succeeds ✅
3. Receipt PDF is generated ✅
4. Email sent with receipt attached ✅
5. User redirected to order page ✅
6. **No more 500 error!** ✅

---

## 📝 Note About Your Keys

Make sure your **live server** `.env` file has:
- `PAYMONGO_MODE=live`
- Your live secret key (starts with `sk_live_`)
- Your live public key (starts with `pk_live_`)

These should match the keys in your PayMongo dashboard (Live mode).
