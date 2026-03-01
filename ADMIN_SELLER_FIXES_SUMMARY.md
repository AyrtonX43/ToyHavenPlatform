# Admin Seller Panel & Philippine Text Encoding Fixes

## Changes Made

### 1. Admin Seller Details Page (`resources/views/admin/sellers/show.blade.php`)

#### ✓ Description Text Justification
- Added `text-align: justify` to the seller description field
- Description text now displays with justified alignment for better readability

#### ✓ Verification Documents Fullscreen View
- Changed document "View" button to open a fullscreen modal instead of new tab
- Modal features:
  - Fullscreen display with dark background
  - Supports both images (JPG, PNG) and PDFs
  - PDF documents display in an embedded iframe
  - Image documents display with proper scaling and centering
  - Download button available in modal footer
  - "Open in New Tab" option for external viewing
  - Clean, professional UI with dark theme

**Before:** Documents opened in new tab with no fullscreen option
**After:** Documents open in fullscreen modal with better viewing experience

### 2. Philippine Text Encoding Fix (Ã± → n)

#### Problem
The Philippine PSGC API returns location names with special characters (ñ, á, é, etc.), but these were being stored incorrectly in the database as:
- `ñ` → `Ã±`
- `Ñ` → `Ã`
- `á` → `Ã¡`
- And other similar encoding issues

#### Solution Components

##### A. Global Helper Function
Created `app/Helpers/TextHelper.php` with `normalizePhilippineText()` function that:
- Converts all special characters to ASCII equivalents
- Handles double-encoded characters (Ã±)
- Handles byte-level encoding issues
- Handles proper UTF-8 characters
- Returns normalized text that's safe for database storage

##### B. Updated Controllers
All controllers that save address/location data now normalize text before saving:

1. **Seller Registration** (`app/Http/Controllers/Seller/RegistrationController.php`)
   - Normalizes: business_name, description, address, region, province, city, barangay

2. **User Address Management** (`app/Http/Controllers/ProfileController.php`)
   - Normalizes: label, address, city, province (in both create and update methods)

3. **Checkout Orders** (`app/Http/Controllers/Toyshop/CheckoutController.php`)
   - Normalizes: shipping_address, shipping_city, shipping_province, shipping_notes

4. **POS Orders** (`app/Http/Controllers/Seller/PosController.php`)
   - Normalizes: shipping_address, shipping_city, shipping_province, shipping_notes

##### C. Updated Registration Form
- Removed duplicate `normalizePhilippineText()` function from Blade template
- Now uses the global helper function from `app/Helpers/TextHelper.php`
- JavaScript `normalizeText()` function continues to normalize dropdown values on frontend

##### D. Composer Autoload
- Updated `composer.json` to autoload the helper file
- Ran `composer dump-autoload` to regenerate autoload files

### 3. Database Fix Tools

#### Option A: SQL Script (Quick Fix)
File: `fix_philippine_encoding.sql`

Run this SQL script to fix all existing data in the database:
```bash
# Method 1: MySQL Command Line
mysql -u root -p toyhaven_local < fix_philippine_encoding.sql

# Method 2: phpMyAdmin
# 1. Open http://localhost/phpmyadmin
# 2. Select 'toyhaven_local' database
# 3. Click 'SQL' tab
# 4. Open fix_philippine_encoding.sql in a text editor
# 5. Copy and paste the entire contents
# 6. Click 'Go' button
```

#### Option B: Artisan Command (Programmatic Fix)
File: `app/Console/Commands/FixPhilippineTextEncoding.php`

Run this command when database is running:
```bash
php artisan fix:philippine-text-encoding
```

### 4. Tables Fixed

The following tables are updated by the fix tools:

1. **sellers**
   - Fields: region, province, city, barangay, address, business_name, description

2. **users**
   - Fields: region, province, city, barangay, address, name

3. **addresses**
   - Fields: city, province, address, label

4. **orders**
   - Fields: shipping_address, shipping_city, shipping_province, shipping_notes

## Testing Checklist

### Admin Panel - Seller Details
- [ ] Navigate to Admin Panel → Sellers → Click on any seller
- [ ] Verify seller description is justified (text aligned evenly on both sides)
- [ ] Click "View" button on any verification document
- [ ] Verify document opens in fullscreen modal
- [ ] Verify modal has dark background
- [ ] Test "Download" button in modal
- [ ] Test "Open in New Tab" button in modal
- [ ] Test "Close" button to dismiss modal

### Philippine Text Encoding
- [ ] Run the SQL script or Artisan command to fix existing data
- [ ] Check admin panel for sellers with Philippine addresses
- [ ] Verify addresses display correctly (e.g., "Bañuelos" shows as "Banuelos", not "BaÃ±uelos")
- [ ] Test new seller registration with locations containing ñ
- [ ] Verify new registrations save correctly without encoding issues
- [ ] Test user address creation/update with special characters
- [ ] Test checkout with addresses containing special characters

## Files Modified

### Views
- `resources/views/admin/sellers/show.blade.php` - Added justify and fullscreen modal

### Controllers
- `app/Http/Controllers/Seller/RegistrationController.php` - Added normalization
- `app/Http/Controllers/ProfileController.php` - Added normalization
- `app/Http/Controllers/Toyshop/CheckoutController.php` - Added normalization
- `app/Http/Controllers/Seller/PosController.php` - Added normalization

### New Files
- `app/Helpers/TextHelper.php` - Global helper function
- `app/Console/Commands/FixPhilippineTextEncoding.php` - Database fix command
- `fix_philippine_encoding.sql` - SQL script for database fix
- `PHILIPPINE_TEXT_ENCODING_FIX.md` - Detailed documentation

### Configuration
- `composer.json` - Added helper file to autoload

## Prevention
All new data will be automatically normalized before saving, preventing future encoding issues.

## Notes
- The fix converts special characters to ASCII equivalents (ñ → n) rather than fixing the encoding
- This is a practical solution that prevents database encoding issues
- All Philippine location names will use simplified ASCII characters
- The PSGC API data is normalized on both frontend (JavaScript) and backend (PHP)
