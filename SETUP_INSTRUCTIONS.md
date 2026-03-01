# Setup Instructions for Seller Registration Updates

## Quick Start Guide

### Step 1: Start Your Development Environment

1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**
4. Wait for both services to show green "Running" status

### Step 2: Run Database Migration

Open your terminal/command prompt in the project directory and run:

```bash
php artisan migrate
```

Expected output:
```
INFO  Running migrations.

2026_03_01_202751_add_social_media_and_location_fields_to_sellers_table .... DONE
```

### Step 3: Clear Application Cache (Optional but Recommended)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 4: Test the Registration Forms

#### Test Local Business Toyshop Registration:
1. Open your browser and navigate to: `http://localhost/ToyHavenPlatform/public/seller/register`
2. Click **"Register as Local Business Toyshop"**
3. Fill out the form and test all features

#### Test Verified Trusted Toyshop Registration:
1. From the same page, click **"Register as Verified Trusted Toyshop"**
2. Fill out the form (note the additional document requirements)
3. Test all features

## What to Test

### ✅ Business Description
- [ ] Text appears justified
- [ ] Field is required
- [ ] Can enter up to 2000 characters

### ✅ Business Address
- [ ] Region dropdown loads all Philippine regions
- [ ] Selecting region enables and loads provinces
- [ ] Selecting province enables and loads cities
- [ ] Selecting city enables and loads barangays
- [ ] Postal code only accepts 4 digits
- [ ] Pre-filled data appears if user has existing address
- [ ] Can edit pre-filled data

### ✅ Document Uploads
- [ ] Can upload Primary ID
- [ ] Can upload Facial Verification (selfie with ID)
- [ ] Can upload Bank Statement
- [ ] **For Verified only**: Can upload Business Permit
- [ ] **For Verified only**: Can upload BIR Certificate
- [ ] **For Verified only**: Can upload Product Sample
- [ ] Preview appears after upload
- [ ] Image files show thumbnail preview
- [ ] Can click "Change" to replace document
- [ ] File size validation works (max 5MB)

### ✅ Toy Categories
- [ ] Categories display with icons and descriptions
- [ ] Can select 1-3 categories
- [ ] Counter shows number of selected categories
- [ ] Cannot select more than 3 categories
- [ ] Selected categories highlight in blue
- [ ] Hover effect works on category cards

### ✅ Social Media Links
- [ ] Can enter Facebook URL (optional)
- [ ] Can enter Instagram URL (optional)
- [ ] Can enter TikTok URL (optional)
- [ ] Can enter Website URL (optional)
- [ ] URL validation works

### ✅ Form Submission
- [ ] Form validates all required fields
- [ ] Shows error messages for invalid data
- [ ] Successfully creates seller account
- [ ] Redirects to seller dashboard
- [ ] Shows success message

## Troubleshooting

### Issue: "No connection could be made" error
**Solution**: Make sure MySQL is running in XAMPP Control Panel

### Issue: "Migration not found" error
**Solution**: Make sure you're in the correct project directory

### Issue: "Class 'Category' not found" error
**Solution**: Run `php artisan config:clear` and try again

### Issue: Address dropdowns not loading
**Solution**: 
1. Check your internet connection (API requires internet)
2. Open browser console (F12) and check for JavaScript errors
3. Verify PSGC Cloud API is accessible: https://psgc.cloud/api/regions

### Issue: File upload not working
**Solution**: 
1. Check `storage/app/public/` directory exists
2. Run: `php artisan storage:link`
3. Check file permissions on storage directory

### Issue: Categories not showing
**Solution**: 
1. Make sure you have categories in the database
2. Run: `php artisan db:seed` if needed
3. Check categories are marked as active (`is_active = 1`)

## Database Rollback (If Needed)

If you need to undo the migration:

```bash
php artisan migrate:rollback --step=1
```

This will remove the new fields from the sellers table.

## Additional Commands

### Check Migration Status
```bash
php artisan migrate:status
```

### View Routes
```bash
php artisan route:list --name=seller
```

### Clear All Caches
```bash
php artisan optimize:clear
```

## Support Files Created

1. **SELLER_REGISTRATION_UPDATE_SUMMARY.md** - Detailed documentation of all changes
2. **SETUP_INSTRUCTIONS.md** (this file) - Quick setup guide

## Next Steps After Testing

1. Test with real data
2. Verify documents are stored correctly in `storage/app/public/seller_documents/`
3. Check admin panel can view submitted registrations
4. Test email notifications (if configured)
5. Test on mobile devices for responsive design

## Contact

If you encounter any issues not covered here, please check:
- Laravel logs: `storage/logs/laravel.log`
- Browser console for JavaScript errors (F12)
- Network tab in browser dev tools for API calls

---

**Important**: Make sure to test both registration types (Local Business and Verified Trusted) to ensure all features work correctly!
