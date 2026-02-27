# PayMongo Setup Guide for Thesis Project

## For Students / Thesis Projects

### Option 1: Test Mode (Recommended) ✅

Perfect for thesis demonstrations without real money.

#### Step 1: Get Test API Keys

1. Go to [PayMongo Dashboard](https://dashboard.paymongo.com/)
2. **Switch to "Test Mode"** (toggle at the top right)
3. Navigate to **Developers → API Keys**
4. Copy your keys:
   - Test Public Key: `pk_test_xxxxxxxxxx`
   - Test Secret Key: `sk_test_xxxxxxxxxx`

#### Step 2: Update Your .env File

```env
APP_ENV=local
# or APP_ENV=development

PAYMONGO_PUBLIC_KEY=pk_test_your_actual_test_public_key_here
PAYMONGO_SECRET_KEY=sk_test_your_actual_test_secret_key_here
```

#### Step 3: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### Step 4: Test Payment Methods

##### Credit/Debit Card (Test Cards)

| Card Number | Expiry | CVC | Result |
|-------------|--------|-----|--------|
| `4343 4343 4343 4345` | Any future date | Any 3 digits | ✅ Success (no 3DS) |
| `4120 0000 0000 0007` | Any future date | Any 3 digits | ✅ Success (with 3DS) |
| `4571 7360 0000 0008` | Any future date | Any 3 digits | ❌ Card Declined |
| `4000 0200 0000 0000` | Any future date | Any 3 digits | ❌ Insufficient Funds |

**For 3DS Testing:**
- After entering card `4120 0000 0000 0007`, you'll be redirected to a 3DS page
- Click **"Authorize Test Payment"** to succeed
- Click **"Fail Test Payment"** to simulate failure

##### GCash (Test Mode)

In test mode, GCash works automatically:
1. Select GCash as payment method
2. You'll be redirected to a test GCash page
3. Click **"Authorize Test Payment"** to complete

##### Maya/PayMaya (Test Mode)

In test mode, Maya works automatically:
1. Select Maya as payment method
2. You'll be redirected to a test Maya page
3. Click **"Authorize Test Payment"** to complete

---

### Option 2: Live Mode (Production)

⚠️ **Only use this if your thesis committee requires real transactions**

#### Requirements for Live Mode:

1. **Account Verification:**
   - Valid Government ID (Driver's License, Passport, etc.)
   - Proof of Address
   - Business Registration:
     - DTI Registration (for Sole Proprietor) - ₱500-₱700
     - OR SEC Registration (for Corporation)
     - OR Mayor's Permit

2. **Bank Account:**
   - For receiving settlements
   - Must match the registered business name

3. **Activate Payment Methods:**
   - Go to [PayMongo Dashboard](https://dashboard.paymongo.com/)
   - Switch to **"Live Mode"**
   - Navigate to **Settings → Payment Methods**
   - Enable:
     - ✅ Cards (Visa/Mastercard)
     - ✅ GCash
     - ✅ Maya (PayMaya)
   - Follow the activation requirements for each method

4. **Update .env for Live:**
   ```env
   APP_ENV=production
   
   PAYMONGO_PUBLIC_KEY=pk_live_your_actual_live_public_key_here
   PAYMONGO_SECRET_KEY=sk_live_your_actual_live_secret_key_here
   ```

#### Live Mode Payment Method Activation:

**GCash:**
- Available after account verification
- No additional fees for activation
- Transaction fee: 2.5% per transaction

**Maya (PayMaya):**
- Available after account verification
- No additional fees for activation
- Transaction fee: 2.2% per transaction

**Cards:**
- Available immediately after verification
- Transaction fee: 3.5% + ₱15 per transaction

---

## Troubleshooting

### "payment method is not allowed" Error

**In Test Mode:**
- Make sure you're using test API keys (`pk_test_...` and `sk_test_...`)
- All payment methods (Card, GCash, Maya) work automatically in test mode

**In Live Mode:**
- Your PayMongo account needs to be verified
- Payment methods must be activated in Dashboard → Settings → Payment Methods
- If you just activated them, wait 5-10 minutes for changes to propagate

### "client_key is required" Error

- This is fixed in the latest code
- Make sure you've pulled the latest changes from git
- Run `php artisan cache:clear` and `php artisan view:clear`

### Testing 3DS Redirect

- Use card `4120 0000 0000 0007` in test mode
- You should be redirected to a test 3DS page
- Click "Authorize Test Payment" to complete

---

## Recommended Setup for Thesis Defense

1. **Use Test Mode** for the actual demonstration
2. **Prepare Test Scenarios:**
   - Successful card payment (no 3DS): `4343 4343 4343 4345`
   - Successful card payment (with 3DS): `4120 0000 0000 0007`
   - Failed payment (declined card): `4571 7360 0000 0008`
   - GCash payment (test mode)
   - Maya payment (test mode)

3. **Documentation for Panel:**
   - Explain that you're using PayMongo's official test environment
   - Show that the integration follows PayMongo's best practices
   - Demonstrate the complete payment flow including 3DS authentication
   - Explain that moving to production only requires:
     - Account verification
     - Switching to live API keys
     - No code changes needed

4. **What to Tell Your Panel:**
   - "This system uses PayMongo, a legitimate payment gateway in the Philippines"
   - "We're using their official test environment for demonstration"
   - "The code is production-ready and only needs live API keys to go live"
   - "All payment methods (Card, GCash, Maya) are integrated and tested"

---

## Support

- **PayMongo Documentation:** https://developers.paymongo.com/
- **PayMongo Support:** support@paymongo.com
- **Test Mode Guide:** https://developers.paymongo.com/docs/testing

---

## Quick Start Checklist

- [ ] Sign up at https://dashboard.paymongo.com/
- [ ] Switch to Test Mode
- [ ] Copy Test API Keys
- [ ] Update .env with test keys
- [ ] Run `php artisan config:clear`
- [ ] Test with card `4343 4343 4343 4345`
- [ ] Test 3DS with card `4120 0000 0000 0007`
- [ ] Test GCash (test mode)
- [ ] Test Maya (test mode)
- [ ] Document the flow for your thesis paper

Good luck with your thesis defense! 🎓
