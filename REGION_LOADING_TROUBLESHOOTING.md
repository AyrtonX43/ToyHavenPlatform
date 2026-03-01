# Region Loading Troubleshooting Guide

## Issue: Regions Not Showing in Dropdown

If the region dropdown is empty or not loading, follow these steps:

---

## Step 1: CLEAR YOUR BROWSER CACHE (CRITICAL!)

The JavaScript has been updated multiple times. You **MUST** clear your cache:

### Method 1: Hard Refresh (Fastest)
- **Windows**: `Ctrl + Shift + R`
- **Mac**: `Cmd + Shift + R`

### Method 2: Clear Cache Completely
1. Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
2. Select "Cached images and files"
3. Select "All time" or "Last hour"
4. Click "Clear data"

### Method 3: Use Incognito/Private Mode
1. Open new incognito/private window
2. Navigate to the registration form
3. Test if regions load

---

## Step 2: Check Browser Console

1. **Open Developer Tools**: Press `F12` or right-click → "Inspect"
2. **Click "Console" tab**
3. **Refresh the page**
4. **Look for these messages**:

### ✅ Success Messages (What you SHOULD see):
```
Seller Registration Form Script Loaded - Version: 2.2-[timestamp]
Starting to load regions from: https://psgc.cloud/api
Regions API response status: 200
Regions loaded successfully: 17 regions
Region options added: 18
```

### ❌ Error Messages (What indicates a problem):
```
Error loading regions: [error message]
HTTP error! status: 404
No regions data received!
Region select element not found!
```

---

## Step 3: Diagnose the Problem

### Problem A: "Region select element not found!"
**Cause**: JavaScript is loading before the HTML element exists

**Solution**:
1. Refresh the page
2. Clear cache and try again
3. Check if you're on the correct page (`/seller/register?type=basic` or `?type=verified`)

### Problem B: "HTTP error! status: 404" or "Failed to fetch"
**Cause**: Cannot reach the PSGC Cloud API

**Solution**:
1. Check your internet connection
2. Try accessing https://psgc.cloud/api/regions directly in your browser
3. Check if your firewall is blocking the API
4. Try a different network (mobile hotspot)

### Problem C: "No regions data received!"
**Cause**: API returned empty data

**Solution**:
1. Check if PSGC API is down: https://psgc.cloud/
2. Wait a few minutes and try again
3. Check browser console for more details

### Problem D: Regions still not showing after cache clear
**Cause**: Aggressive browser caching or service worker

**Solution**:
1. Close ALL browser tabs/windows
2. Reopen browser
3. Try incognito mode
4. Try a different browser (Chrome, Firefox, Edge)

---

## Step 4: Manual Testing

### Test the API Directly:
1. Open a new browser tab
2. Go to: https://psgc.cloud/api/regions
3. You should see JSON data with 17 Philippine regions
4. If you see data here but not in the form, it's a caching issue

### Expected API Response:
```json
[
  {
    "code": "010000000",
    "name": "Region I (Ilocos Region)",
    "regionName": "Region I",
    "islandGroupCode": "L",
    "psgc10DigitCode": "0100000000"
  },
  ...
]
```

---

## Step 5: Check Network Tab

1. Open Developer Tools (F12)
2. Click "Network" tab
3. Refresh the page
4. Look for request to `psgc.cloud/api/regions`
5. Click on it to see:
   - Status: Should be `200 OK`
   - Response: Should show JSON data
   - If status is red or failed, there's a network issue

---

## Step 6: Verify Script Version

In the console, you should see:
```
Seller Registration Form Script Loaded - Version: 2.2-[timestamp]
```

If you see an older version number (2.0, 2.1) or no version at all:
1. Your cache is not cleared
2. Follow Step 1 again more carefully
3. Try incognito mode

---

## Quick Fixes Checklist

- [ ] Hard refresh: `Ctrl + Shift + R`
- [ ] Clear browser cache completely
- [ ] Try incognito/private mode
- [ ] Check console for errors (F12)
- [ ] Test API directly: https://psgc.cloud/api/regions
- [ ] Check internet connection
- [ ] Try different browser
- [ ] Close all tabs and reopen
- [ ] Disable browser extensions
- [ ] Check if ad blocker is interfering

---

## For Developers

### If regions are still not loading after all above steps:

1. **Check the HTML**:
   ```html
   <select name="region" id="region" class="form-select" required>
       <option value="">Loading regions...</option>
   </select>
   ```

2. **Check JavaScript is loading**:
   ```javascript
   console.log('Script loaded');
   ```

3. **Check API response**:
   ```javascript
   fetch('https://psgc.cloud/api/regions')
     .then(r => r.json())
     .then(d => console.log(d));
   ```

4. **Check for JavaScript errors**:
   - Open console (F12)
   - Look for red error messages
   - Fix any syntax errors

5. **Verify element exists**:
   ```javascript
   console.log(document.getElementById('region'));
   ```

---

## Server-Side Checks (Putty)

```bash
# Pull latest changes
cd /path/to/ToyHavenPlatform
git pull origin main

# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Check if views are compiled
php artisan view:cache

# Restart web server if needed
# For Apache:
sudo service apache2 restart
# For Nginx:
sudo service nginx restart
```

---

## Still Not Working?

### Last Resort Options:

1. **Use a different device** (phone, tablet, different computer)
2. **Use a different network** (mobile data instead of WiFi)
3. **Wait 5-10 minutes** (sometimes CDN/cache needs time)
4. **Contact support** with:
   - Browser name and version
   - Console error messages (screenshot)
   - Network tab screenshot
   - What you see in the dropdown

---

## Expected Behavior

When working correctly:
1. Page loads
2. Region dropdown shows "Loading regions..."
3. After 1-2 seconds, dropdown populates with 17 Philippine regions
4. You can select a region
5. Province dropdown enables and loads provinces
6. You can select province
7. City dropdown enables and loads cities
8. You can select city
9. Barangay dropdown enables and loads barangays

---

## Common Mistakes

❌ **Not clearing cache properly** - Most common issue!  
❌ **Testing in same tab** - Open new tab after clearing cache  
❌ **Ad blocker blocking API** - Disable temporarily  
❌ **VPN interfering** - Disable temporarily  
❌ **Old browser version** - Update browser  
❌ **JavaScript disabled** - Enable JavaScript  

---

**Last Updated**: March 1, 2026  
**Script Version**: 2.2  
**Commit**: b786b35
