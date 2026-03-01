# Fix Migration Conflict - order_disputes Table

## Problem
The `order_disputes` table already exists in your database, causing the migration to fail.

## Solution Options

### Option 1: Drop the Existing Table (Recommended if it's empty/test data)

```bash
# Connect to MySQL
mysql -u u334258035_toyhaven -p u334258035_toyhaven

# Check if table has data
SELECT COUNT(*) FROM order_disputes;

# If empty or you don't need the data, drop it
DROP TABLE IF EXISTS order_disputes;

# Exit MySQL
exit;

# Run migrations again
php artisan migrate
```

### Option 2: Mark Migration as Already Run (If table structure is correct)

```bash
# Check the migrations table to see what's been run
php artisan migrate:status

# If the structure is already correct, manually mark it as migrated
mysql -u u334258035_toyhaven -p u334258035_toyhaven

# Insert the migration record
INSERT INTO migrations (migration, batch) 
VALUES ('2026_03_01_084334_create_order_disputes_table', 
        (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations AS m));

exit;

# Continue with remaining migrations
php artisan migrate
```

### Option 3: Fresh Migration (CAUTION: Deletes ALL data)

```bash
# This will drop ALL tables and re-run all migrations
# USE ONLY ON DEVELOPMENT/STAGING, NOT PRODUCTION!
php artisan migrate:fresh
```

### Option 4: Rollback and Re-run (Safest for Production)

```bash
# Rollback the last batch of migrations
php artisan migrate:rollback --step=1

# Run migrations again
php artisan migrate
```

## Recommended Steps for Your Case

Since you're on the live server and the table exists, I recommend **Option 2**:

```bash
# 1. Check current migration status
php artisan migrate:status

# 2. Connect to MySQL
mysql -u u334258035_toyhaven -p u334258035_toyhaven

# 3. Check if order_disputes table exists and its structure
DESCRIBE order_disputes;

# 4. If structure looks correct, mark migration as done
INSERT INTO migrations (migration, batch) 
VALUES ('2026_03_01_084334_create_order_disputes_table', 
        (SELECT IFNULL(MAX(batch), 0) + 1 FROM (SELECT * FROM migrations) AS m));

# 5. Exit MySQL
exit;

# 6. Continue migrations
php artisan migrate
```

## Quick Fix Command (Copy-Paste)

```bash
# Mark the migration as already run
php artisan tinker
DB::table('migrations')->insert([
    'migration' => '2026_03_01_084334_create_order_disputes_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
exit

# Then run migrations again
php artisan migrate
```

## Verify After Fix

```bash
# Check all migrations are complete
php artisan migrate:status

# Verify the table structure
mysql -u u334258035_toyhaven -p u334258035_toyhaven -e "DESCRIBE order_disputes"
```

## If You Need to Start Fresh (Development Only)

```bash
# Drop the problematic table
mysql -u u334258035_toyhaven -p u334258035_toyhaven -e "DROP TABLE IF EXISTS order_disputes"

# Run migrations
php artisan migrate
```
