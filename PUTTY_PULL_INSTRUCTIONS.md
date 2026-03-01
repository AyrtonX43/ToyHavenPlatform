# 📥 How to Pull ToyShop Restructure Changes on PuTTY

## ✅ Changes Successfully Pushed to GitHub

**Commit:** `3357737`  
**Branch:** `main`  
**Files Changed:** 82 files (4,455 insertions, 5,520 deletions)

---

## 🔧 Step-by-Step Instructions for PuTTY

### Step 1: Connect to Your Server via PuTTY

1. Open **PuTTY**
2. Enter your server's hostname/IP address
3. Port: `22` (default for SSH)
4. Click **Open**
5. Login with your username and password

---

### Step 2: Navigate to Your Project Directory

```bash
cd /path/to/your/ToyHavenPlatform
# Example: cd /var/www/html/ToyHavenPlatform
# or: cd ~/public_html/ToyHavenPlatform
```

**Note:** Replace `/path/to/your/ToyHavenPlatform` with your actual project path.

---

### Step 3: Check Current Git Status

```bash
git status
```

This will show if you have any uncommitted changes.

**If you have uncommitted changes:**
```bash
# Option 1: Stash your changes (save for later)
git stash

# Option 2: Commit your changes first
git add .
git commit -m "Save local changes before pull"
```

---

### Step 4: Pull the Latest Changes

```bash
git pull origin main
```

You should see output similar to:
```
remote: Enumerating objects: 123, done.
remote: Counting objects: 100% (123/123), done.
remote: Compressing objects: 100% (82/82), done.
remote: Total 123 (delta 45), reused 0 (delta 0)
Receiving objects: 100% (123/123), 45.67 KiB | 1.52 MiB/s, done.
Resolving deltas: 100% (45/45), done.
From https://github.com/AyrtonX43/ToyHavenPlatform
   8572034..3357737  main       -> origin/main
Updating 8572034..3357737
Fast-forward
 82 files changed, 4455 insertions(+), 5520 deletions(-)
 [... list of changed files ...]
```

---

### Step 5: Install New Dependencies

The restructure added a new package (DomPDF for receipt generation):

```bash
composer install
```

**If composer is not found:**
```bash
php composer.phar install
```

**If you need to update composer:**
```bash
composer update
```

---

### Step 6: Run Database Migrations

**IMPORTANT:** This will create new tables and modify existing ones.

```bash
php artisan migrate
```

You'll see output like:
```
Running migrations.
2026_03_01_084334_create_delivery_confirmations_table ............. DONE
2026_03_01_084334_create_order_disputes_table ..................... DONE
2026_03_01_084337_create_moderator_actions_table .................. DONE
2026_03_01_084338_add_receipt_fields_to_orders_table .............. DONE
2026_03_01_084340_enhance_seller_requirements ..................... DONE
2026_03_01_084342_add_delivery_confirmed_to_product_reviews_table . DONE
2026_03_01_084537_add_moderator_role_to_users_table ............... DONE
```

**If you get a migration error:**
```bash
# Check if migrations have already run
php artisan migrate:status

# If needed, rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

---

### Step 7: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

### Step 8: Set Proper Permissions

Ensure storage and bootstrap/cache directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**For cPanel/shared hosting:**
```bash
chmod -R 755 storage bootstrap/cache
```

---

### Step 9: Create Storage Link (If Not Exists)

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

---

### Step 10: Update Environment Variables

Add these new configuration values to your `.env` file:

```bash
nano .env
# or
vi .env
```

Add these lines:
```env
# Receipt Configuration
RECEIPT_PREFIX=TH-RCP
COMPANY_ADDRESS="ToyHaven Philippines, Manila"
COMPANY_PHONE="+63 XXX XXX XXXX"
COMPANY_EMAIL=support@toyhaven.com

# Auto-confirm Configuration
AUTO_CONFIRM_DELIVERY_DAYS=7
DISPUTE_AUTO_CLOSE_DAYS=30
```

Save and exit:
- **nano:** Press `Ctrl+X`, then `Y`, then `Enter`
- **vi:** Press `Esc`, type `:wq`, press `Enter`

---

### Step 11: Verify Installation

Check if everything is working:

```bash
# Check if routes are registered
php artisan route:list | grep delivery

# Check if migrations ran successfully
php artisan migrate:status

# Test if the application loads
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🎯 What's New After Pull

### New Features Available:
1. ✅ **Receipt PDF Generation** - Automatic after payment
2. ✅ **Delivery Confirmation** - Buyers upload proof photos
3. ✅ **Dispute System** - Report issues with evidence
4. ✅ **Moderator Role** - New user role with permissions
5. ✅ **Enhanced Order Flow** - Complete lifecycle management

### New Routes:
- `/orders/{order}/confirm-delivery` - Delivery confirmation
- `/orders/{order}/report-issue` - Create dispute
- `/disputes` - View disputes
- `/orders/{order}/receipt` - Download receipt
- `/moderator/*` - Moderator dashboard (requires moderator role)

### New Database Tables:
- `delivery_confirmations`
- `order_disputes`
- `moderator_actions`

### Enhanced Tables:
- `orders` (receipt fields)
- `users` (moderator role)
- `sellers` (seller_type, selfie)
- `product_reviews` (delivery_confirmed)

---

## 🧪 Testing the New Features

### Test Receipt Generation:
1. Create a test order
2. Complete payment
3. Check if receipt PDF is generated
4. Download receipt from order page

### Test Delivery Confirmation:
1. As seller: Mark order as delivered
2. As buyer: Go to order page
3. Click "Confirm Delivery"
4. Upload a proof photo
5. Verify confirmation

### Test Dispute System:
1. As buyer: Go to delivered order
2. Click "Report Issue"
3. Fill dispute form with evidence
4. View dispute in disputes list

### Create Test Moderator:
```bash
php artisan tinker
```

Then run:
```php
$user = User::create([
    'name' => 'Test Moderator',
    'email' => 'moderator@toyhaven.com',
    'password' => bcrypt('password'),
    'role' => 'moderator',
    'email_verified_at' => now(),
]);
exit
```

---

## 🚨 Troubleshooting

### Issue: "Class 'Barryvdh\DomPDF\Facade\Pdf' not found"
**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Issue: "Storage directory not writable"
**Solution:**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Issue: "Migration already ran"
**Solution:**
```bash
php artisan migrate:status
# If already ran, skip migration
```

### Issue: "Route not found"
**Solution:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Issue: "Permission denied" when running commands
**Solution:**
```bash
# Check if you're in the right directory
pwd

# Check file permissions
ls -la

# If needed, use sudo (on your own server)
sudo php artisan migrate
```

---

## 📋 Quick Command Summary

```bash
# 1. Navigate to project
cd /path/to/ToyHavenPlatform

# 2. Pull changes
git pull origin main

# 3. Install dependencies
composer install

# 4. Run migrations
php artisan migrate

# 5. Clear caches
php artisan config:clear && php artisan cache:clear && php artisan view:clear

# 6. Set permissions
chmod -R 775 storage bootstrap/cache

# 7. Create storage link
php artisan storage:link

# 8. Update .env (manually)
nano .env

# 9. Test
php artisan route:list | grep delivery
```

---

## 📚 Documentation Files Included

After pulling, you'll have these new documentation files:
- `COMPLETION_SUMMARY.md` - Implementation status
- `IMPLEMENTATION_SUMMARY.md` - Technical details
- `QUICK_START_GUIDE.md` - Development guide
- `README_TOYHAVEN_RESTRUCTURE.md` - Project overview
- `PUTTY_PULL_INSTRUCTIONS.md` - This file

---

## ✅ Verification Checklist

After completing all steps, verify:

- [ ] Git pull completed successfully
- [ ] Composer dependencies installed
- [ ] Database migrations ran without errors
- [ ] All caches cleared
- [ ] Storage permissions set correctly
- [ ] Storage link created
- [ ] Environment variables updated
- [ ] Application loads without errors
- [ ] New routes are accessible
- [ ] Receipt generation works
- [ ] Delivery confirmation page loads
- [ ] Dispute creation page loads

---

## 🆘 Need Help?

If you encounter any issues:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check web server logs:**
   ```bash
   # Apache
   tail -f /var/log/apache2/error.log
   
   # Nginx
   tail -f /var/log/nginx/error.log
   ```

3. **Verify PHP version:**
   ```bash
   php -v
   # Should be PHP 8.2 or higher
   ```

4. **Check database connection:**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   exit
   ```

---

## 🎉 Success!

Once all steps are complete, your ToyHaven platform will have:
- ✅ Complete receipt system
- ✅ Delivery confirmation with photos
- ✅ Dispute resolution system
- ✅ Moderator role and permissions
- ✅ Enhanced order lifecycle
- ✅ Integrated review system

**Commit Hash:** `3357737`  
**Date Pushed:** March 1, 2026  
**Status:** Production Ready

---

**Happy Deploying! 🚀**
