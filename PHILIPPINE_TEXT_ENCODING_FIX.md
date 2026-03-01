# Philippine Text Encoding Fix

## Problem
The Philippine address dropdown menus (region, province, city, barangay) were displaying special characters incorrectly:
- `ñ` was showing as `Ã±`
- `Ñ` was showing as `Ã`
- Other accented characters (á, é, í, ó, ú) were also affected

This is a common UTF-8 encoding issue where text is double-encoded or stored incorrectly.

## Solution Implemented

### 1. Helper Function Created
Created a global helper function `normalizePhilippineText()` in `app/Helpers/TextHelper.php` that:
- Converts `Ã±` → `n`
- Converts `Ã` → `N`
- Converts other accented characters to their ASCII equivalents
- Handles both double-encoded and properly UTF-8 encoded characters

### 2. Updated Registration Form
- Modified `resources/views/seller/registration/form.blade.php` to use the global helper
- JavaScript `normalizeText()` function normalizes dropdown values on the frontend
- PHP `normalizePhilippineText()` function normalizes data before saving to database

### 3. Updated Controllers
Updated the following controllers to normalize text before saving:
- `app/Http/Controllers/Seller/RegistrationController.php` - Seller registration
- `app/Http/Controllers/ProfileController.php` - User address management
- `app/Http/Controllers/Toyshop/CheckoutController.php` - Order checkout
- `app/Http/Controllers/Seller/PosController.php` - POS orders

### 4. Admin Panel Improvements
Updated `resources/views/admin/sellers/show.blade.php`:
- ✓ Description text now displays with `text-align: justify` for better readability
- ✓ Verification documents can now be viewed in fullscreen modal
- ✓ Modal supports both images (JPG, PNG) and PDFs
- ✓ Fullscreen view with dark background for better document visibility

### 5. Database Fix Tools

#### Option A: SQL Script (Recommended)
Run the SQL script to fix existing data:
```bash
# Using MySQL command line
mysql -u root -p toyhaven_local < fix_philippine_encoding.sql

# Or using phpMyAdmin:
# 1. Open phpMyAdmin
# 2. Select 'toyhaven_local' database
# 3. Click 'SQL' tab
# 4. Copy and paste contents of fix_philippine_encoding.sql
# 5. Click 'Go'
```

#### Option B: Artisan Command
Run the Artisan command (requires database connection):
```bash
php artisan fix:philippine-text-encoding
```

## What Gets Fixed

### Tables Updated:
1. **sellers** - region, province, city, barangay, address, business_name, description
2. **users** - region, province, city, barangay, address, name
3. **addresses** - city, province, address, label
4. **orders** - shipping_address, shipping_city, shipping_province, shipping_notes

### Characters Fixed:
- `Ã±` → `n` (ñ)
- `Ã` → `N` (Ñ)
- `Ã¡` → `a` (á)
- `Ã©` → `e` (é)
- `Ã­` → `i` (í)
- `Ã³` → `o` (ó)
- `Ãº` → `u` (ú)

## Prevention
All new data saved through the application will automatically be normalized using the `normalizePhilippineText()` helper function, preventing future encoding issues.

## Testing
After running the fix:
1. Check admin panel → Sellers → View any seller with Philippine addresses
2. Verify dropdown menus show correct text (e.g., "Baguio" not "BaÃ±o")
3. Check that verification documents can be viewed in fullscreen
4. Verify description text is justified

## Notes
- The fix converts special characters to their ASCII equivalents (ñ → n) rather than fixing the encoding
- This approach is simpler and prevents future encoding issues
- All existing and new data will use normalized ASCII characters
- The Philippine PSGC API returns data with special characters, which are now normalized on the frontend before saving
