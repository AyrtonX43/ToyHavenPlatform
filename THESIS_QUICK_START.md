# 🎓 Quick Start Guide for Thesis Project

## Setup in 5 Minutes

### 1. Get Your Test API Keys

1. Go to https://dashboard.paymongo.com/
2. **Switch to "Test Mode"** (toggle at top right)
3. Go to **Developers → API Keys**
4. Copy both keys

### 2. Update Your `.env` File

```env
APP_ENV=local

PAYMONGO_PUBLIC_KEY=pk_test_paste_your_test_public_key_here
PAYMONGO_SECRET_KEY=sk_test_paste_your_test_secret_key_here
```

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Test It!

**Test Card Numbers:**
- ✅ Success (no 3DS): `4343 4343 4343 4345`
- ✅ Success (with 3DS): `4120 0000 0000 0007`
- ❌ Declined: `4571 7360 0000 0008`

**Expiry:** Any future date (e.g., `12/2028`)  
**CVC:** Any 3 digits (e.g., `123`)

---

## What You'll See

### Payment Methods Available in Test Mode:
- 💳 **Credit/Debit Card** (Visa, Mastercard)
- 📱 **GCash** (e-wallet)
- 💰 **Maya/PayMaya** (e-wallet)

### Testing 3DS Authentication:
1. Use card `4120 0000 0000 0007`
2. You'll be redirected to a test 3DS page
3. Click **"Authorize Test Payment"** to succeed
4. Or click **"Fail Test Payment"** to simulate failure

### Testing E-Wallets:
1. Select GCash or Maya
2. You'll be redirected to a test page
3. Click **"Authorize Test Payment"** to complete

---

## For Your Thesis Defense

### What to Say:
- "We're using PayMongo, a legitimate payment gateway in the Philippines"
- "This is their official test environment for development"
- "The system supports Card, GCash, and Maya payments"
- "3D Secure authentication is implemented for card security"
- "Moving to production only requires account verification and switching to live API keys"

### Demo Scenarios:
1. **Successful Card Payment (Simple):** Use `4343 4343 4343 4345`
2. **Successful Card Payment (3DS):** Use `4120 0000 0000 0007`
3. **Failed Payment:** Use `4571 7360 0000 0008`
4. **GCash Payment:** Select GCash, complete test flow
5. **Maya Payment:** Select Maya, complete test flow

---

## Troubleshooting

### "Payment method is not allowed"
- Make sure you're using **test keys** (`pk_test_...`)
- Make sure `APP_ENV=local` in your `.env`
- Clear cache: `php artisan config:clear`

### Not seeing GCash/Maya options?
- Check that you're using test keys
- Check that `APP_ENV=local` (not `production`)
- Pull latest code from git

### 3DS redirect not working?
- Make sure you pulled the latest code
- Clear cache and try again
- Use test card `4120 0000 0000 0007`

---

## Moving to Production (After Graduation)

If you want to make this live after your thesis:

1. **Verify Your PayMongo Account:**
   - Submit Government ID
   - Register business (DTI for Sole Proprietor)
   - Add bank account

2. **Activate Payment Methods:**
   - Go to Dashboard → Settings → Payment Methods
   - Enable Card, GCash, Maya

3. **Switch to Live Keys:**
   ```env
   APP_ENV=production
   PAYMONGO_PUBLIC_KEY=pk_live_your_live_key
   PAYMONGO_SECRET_KEY=sk_live_your_live_key
   ```

4. **Deploy:**
   ```bash
   git pull
   php artisan config:clear
   ```

---

## Need Help?

- 📖 Full Guide: `PAYMONGO_SETUP_GUIDE.md`
- 🌐 PayMongo Docs: https://developers.paymongo.com/
- 📧 PayMongo Support: support@paymongo.com

**Good luck with your thesis defense! 🎓**
