# Seller Registration Update Summary

## Overview
Updated the ToyHaven seller registration system to support two types of seller registrations with enhanced features:
1. **Local Business Toyshop** - For small local toy businesses
2. **Verified Trusted Toyshop** - For established businesses with proper documentation

## Changes Made

### 1. Database Changes

#### Migration Created
- **File**: `database/migrations/2026_03_01_202751_add_social_media_and_location_fields_to_sellers_table.php`
- **New Fields Added to `sellers` table**:
  - `region` (string, nullable) - Philippine region
  - `barangay` (string, nullable) - Barangay/district
  - `facebook_url` (string, nullable) - Facebook page URL
  - `instagram_url` (string, nullable) - Instagram profile URL
  - `tiktok_url` (string, nullable) - TikTok profile URL
  - `website_url` (string, nullable) - Business website URL

#### Model Updated
- **File**: `app/Models/Seller.php`
- Added new fields to `$fillable` array

### 2. Frontend Changes

#### Registration Index Page (`resources/views/seller/registration/index.blade.php`)
- Updated button labels to reflect "Local Business Toyshop" and "Verified Trusted Toyshop"
- Added comparison cards showing differences between registration types
- Enhanced visual presentation with icons and descriptions

#### Registration Form (`resources/views/seller/registration/form.blade.php`)

##### Business Description
- ✅ Made required field
- ✅ Added `text-align: justify` style for justified formatting
- ✅ Increased rows to 5 for better input space
- ✅ Added helpful description text

##### Business Address
- ✅ Restructured address input:
  - Single textbox for House/Apartment No., Building/Street, Residence (combined)
  - Cascading dropdowns for Philippine locations:
    - Region dropdown
    - Province dropdown (loads based on region)
    - City/Municipality dropdown (loads based on province)
    - Barangay dropdown (loads based on city)
  - 4-digit postal code field with validation
- ✅ Integrated with PSGC Cloud API (https://psgc.cloud/api) for complete Philippine location data
- ✅ Pre-fills from user's existing address (if available)
- ✅ Allows editing of pre-filled data

##### Required Documents
Updated document requirements with preview and edit functionality:

**For Local Business Toyshop:**
- ✅ Primary ID (required) - with preview
- ✅ Facial Verification (Selfie with ID) (required) - with preview
- ✅ Bank Statement (required) - with preview

**For Verified Trusted Toyshop (additional documents):**
- ✅ Business Permit (required) - with preview
- ✅ BIR Certificate of Registration (required) - with preview
- ✅ Product Sample (required) - with preview

**Document Upload Features:**
- ✅ Real-time preview for uploaded files
- ✅ Image preview for JPG/PNG files
- ✅ File name and size display
- ✅ "Change" button to replace uploaded documents
- ✅ File size validation (5MB max)
- ✅ File type validation

##### Toy Categories Selection
- ✅ Enhanced card-based design with:
  - Category icon (Bootstrap Icons)
  - Category title
  - Short description
- ✅ Limit selection to 1-3 categories
- ✅ Real-time counter showing selected categories
- ✅ Auto-disable unselected categories when 3 are selected
- ✅ Visual feedback with color changes when selected
- ✅ Hover effects for better UX

##### Social Media Links (NEW)
- ✅ Facebook Page URL (optional)
- ✅ Instagram URL (optional)
- ✅ TikTok URL (optional)
- ✅ Website URL (optional)
- ✅ URL validation
- ✅ Icons for each platform

### 3. Backend Changes

#### Controller Updates (`app/Http/Controllers/Seller/RegistrationController.php`)

##### Validation Rules Updated:
- Added `region`, `barangay` as required fields
- Made `description` required with max 2000 characters
- Added `toy_category_ids` validation (required, array, min:1, max:3)
- Added `facial_verification` as required document
- Updated postal code validation to exactly 4 digits
- Added social media URL validation (optional)
- Added verified shop document validation:
  - `business_permit` (required for verified)
  - `bir_certificate` (required for verified)
  - `product_sample` (required for verified)

##### Data Storage Updated:
- Added new address fields (region, barangay) to seller creation
- Added social media URLs to seller creation
- Updated document storage to handle new document types:
  - `facial_verification`
  - `business_permit`
  - `bir_certificate`
  - `product_sample`

##### Pre-fill Data Enhanced:
- Added region and barangay to pre-filled data from user profile

### 4. JavaScript Enhancements

#### Philippine Address API Integration
- ✅ Integrated with PSGC Cloud API for real-time location data
- ✅ Cascading dropdowns (Region → Province → City → Barangay)
- ✅ Automatic loading of child locations based on parent selection
- ✅ Pre-selection of old values on validation errors
- ✅ Proper error handling

#### Document Upload Management
- ✅ File preview system with image display
- ✅ File size formatting (Bytes, KB, MB)
- ✅ Change/remove document functionality
- ✅ Client-side file size validation (5MB limit)

#### Category Selection Logic
- ✅ Real-time counter update
- ✅ Automatic disable of unselected categories at 3 selections
- ✅ Form validation to ensure 1-3 categories selected

#### Postal Code Validation
- ✅ Auto-format to numbers only
- ✅ Max 4 digits enforcement

### 5. Styling Enhancements

#### Custom CSS Added:
```css
.category-card - Enhanced card styling with transitions
.category-checkbox:checked + .category-card - Selected state styling
Hover effects for better interactivity
```

## Testing Instructions

### Prerequisites
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Run the migration: `php artisan migrate`

### Test Cases

#### 1. Local Business Toyshop Registration
1. Navigate to `/seller/register`
2. Click "Register as Local Business Toyshop"
3. Fill out the form:
   - Enter business name and description (check justified text)
   - Fill phone and email
   - Enter address details
   - Select Region → Province → City → Barangay (verify cascading works)
   - Enter 4-digit postal code
   - Upload Primary ID (verify preview appears)
   - Upload Facial Verification selfie (verify preview)
   - Upload Bank Statement (verify preview)
   - Select 1-3 toy categories (verify counter and limit)
   - Optionally add social media links
4. Test "Change" button on documents
5. Try selecting more than 3 categories (should be prevented)
6. Submit form and verify success

#### 2. Verified Trusted Toyshop Registration
1. Navigate to `/seller/register`
2. Click "Register as Verified Trusted Toyshop"
3. Fill out the form (same as above, plus):
   - Upload Business Permit (verify preview)
   - Upload BIR Certificate (verify preview)
   - Upload Product Sample (verify preview)
4. Submit form and verify success

#### 3. Address Pre-fill Test
1. Ensure user has a default address in profile
2. Start registration
3. Verify address fields are pre-filled
4. Verify you can edit pre-filled data

#### 4. Validation Tests
- Try submitting without required fields
- Try uploading files larger than 5MB
- Try uploading wrong file types
- Try entering invalid postal code (not 4 digits)
- Try selecting 0 categories
- Try selecting 4+ categories

## API Dependencies

### PSGC Cloud API
- **Base URL**: https://psgc.cloud/api
- **Endpoints Used**:
  - `/regions` - Get all Philippine regions
  - `/regions/{code}/provinces` - Get provinces by region
  - `/provinces/{code}/cities-municipalities` - Get cities by province
  - `/cities-municipalities/{code}/barangays` - Get barangays by city
- **No authentication required**
- **Free to use**

## Files Modified

1. `resources/views/seller/registration/index.blade.php`
2. `resources/views/seller/registration/form.blade.php`
3. `app/Http/Controllers/Seller/RegistrationController.php`
4. `app/Models/Seller.php`
5. `database/migrations/2026_03_01_202751_add_social_media_and_location_fields_to_sellers_table.php` (NEW)

## Database Migration Command

```bash
php artisan migrate
```

## Notes

1. **Philippine Location Data**: Using official PSGC (Philippine Standard Geographic Code) data via PSGC Cloud API
2. **Document Storage**: All documents stored in `storage/app/public/seller_documents/{seller_id}/`
3. **Document Types**: Updated to include new verification types
4. **Social Media Links**: Optional fields, URL validated if provided
5. **Category Selection**: Limited to 1-3 categories with visual feedback
6. **Responsive Design**: All changes are mobile-friendly using Bootstrap 5

## Future Enhancements (Optional)

1. Add real-time postal code lookup based on selected location
2. Add document OCR for automatic ID verification
3. Add social media profile verification
4. Add Google Maps integration for address verification
5. Add drag-and-drop file upload
6. Add webcam capture for facial verification

## Support

For any issues or questions, please refer to:
- PSGC Cloud API Documentation: https://psgc.cloud/api-docs/v2
- Laravel File Upload Documentation
- Bootstrap 5 Documentation for styling
