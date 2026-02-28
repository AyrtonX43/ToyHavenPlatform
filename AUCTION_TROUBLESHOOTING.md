# Auction 500 Error Troubleshooting Guide

## Issue: 500 Server Error After Filling Product Auction and Sending for Approval

### Most Common Causes

#### 1. **Missing Database Migrations**
The auction system requires several database tables and columns. If migrations haven't been run on the production server, you'll get a 500 error.

**Solution:**
```bash
# On your Putty server, run:
cd /path/to/ToyHavenPlatform
git pull origin main
php artisan migrate --force
```

**Required migrations:**
- `2026_02_19_000005_create_auctions_table.php`
- `2026_02_28_000003_add_auction_listing_fields_to_auctions_table.php`
- `2026_02_28_110000_create_auction_category_table.php` (pivot table for multiple categories)

#### 2. **Storage Directory Permissions**
The system needs to upload images and videos to `storage/app/public/`.

**Solution:**
```bash
# On your server:
chmod -R 775 storage
chmod -R 775 bootstrap/cache
php artisan storage:link
```

#### 3. **Missing auction_category Pivot Table**
If you see errors related to `auction_category` table not found, this migration is missing.

**Check if migration ran:**
```bash
php artisan migrate:status | grep auction_category
```

**If not found, run:**
```bash
php artisan migrate --path=database/migrations/2026_02_28_110000_create_auction_category_table.php --force
```

#### 4. **File Upload Size Limits**
Large images or videos may exceed PHP's upload limits.

**Check your php.ini:**
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

**Restart Apache/PHP-FPM after changes:**
```bash
sudo systemctl restart apache2
# or
sudo systemctl restart php8.2-fpm
```

### Debugging Steps

#### Step 1: Check the Laravel Log
```bash
tail -100 storage/logs/laravel.log
```

With the new error handling, you should see detailed error messages like:
```
[2026-02-28] localhost.ERROR: Auction creation failed {"user_id":1,"error":"SQLSTATE[42S02]: Base table or view not found: 1146 Table 'toyhaven.auction_category' doesn't exist",...}
```

#### Step 2: Verify Database Tables Exist
```bash
php artisan tinker
```
```php
Schema::hasTable('auctions');
Schema::hasTable('auction_category');
Schema::hasTable('auction_images');
exit
```

#### Step 3: Test File Uploads
```bash
# Check storage is writable
ls -la storage/app/public/
# Should show: drwxrwxr-x
```

#### Step 4: Check MySQL Connection
```bash
php artisan db:show
```

### Error Messages and Solutions

| Error Message | Cause | Solution |
|--------------|-------|----------|
| `Table 'auction_category' doesn't exist` | Missing pivot table migration | Run `php artisan migrate` |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL not running | Start MySQL: `sudo systemctl start mysql` |
| `Failed to store file` | Storage permissions | Run `chmod -R 775 storage` |
| `Maximum execution time exceeded` | Large file upload | Increase `max_execution_time` in php.ini |
| `POST Content-Length exceeds` | File too large | Increase `post_max_size` in php.ini |

### Testing the Fix

After applying fixes, test the auction creation:

1. Navigate to `/auctions/seller/create`
2. Fill in all required fields:
   - Product name
   - At least 1 category
   - Description
   - Box condition
   - Starting price
   - Bid increment
   - Start date (within 5 days)
   - End date (1-2 days after start)
   - At least 1 product photo
3. Click "Submit for Approval"

**Expected result:** Redirect to `/auctions/seller` with success message "Auction listing submitted for approval!"

**If error persists:** Check `storage/logs/laravel.log` for the detailed error message.

### Quick Fix Commands (Run on Server)

```bash
# Pull latest code
git pull origin main

# Run migrations
php artisan migrate --force

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Ensure storage link exists
php artisan storage:link

# Check migration status
php artisan migrate:status
```

### Still Getting Errors?

1. **Enable detailed error display** (temporarily):
   ```bash
   # In .env file
   APP_DEBUG=true
   ```

2. **Check Apache/Nginx error logs**:
   ```bash
   tail -50 /var/log/apache2/error.log
   # or
   tail -50 /var/log/nginx/error.log
   ```

3. **Verify PHP version** (requires PHP 8.1+):
   ```bash
   php -v
   ```

4. **Check disk space**:
   ```bash
   df -h
   ```

### Contact Support

If none of these solutions work, provide:
- Full error message from `storage/logs/laravel.log`
- Output of `php artisan migrate:status`
- PHP version (`php -v`)
- MySQL version (`mysql --version`)
