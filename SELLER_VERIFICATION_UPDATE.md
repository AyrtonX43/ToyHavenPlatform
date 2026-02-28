# ToyShop Seller Verification Requirements Update

## Overview
Updated ToyShop seller registration requirements to match the auction seller verification process for consistency and enhanced security.

---

## 🔄 Changes Made

### **Previous Requirements:**
- Basic ID document
- Bank account document
- Optional business registration (for verified shops only)

### **New Requirements (Matching Auction System):**

#### **For Individual Sellers:**
✅ **Selfie** (with ID) - Required
✅ **Government ID #1** - Required
✅ **Government ID #2** - Required
✅ **Bank Statement** - Required

#### **For Business Sellers:**
✅ **Selfie** (with ID) - Required
✅ **Government ID #1** - Required
✅ **Government ID #2** - Required
✅ **Bank Statement** - Required
✅ **Business Permit** - Required
✅ **BIR Certificate of Registration** - Required
✅ **Official Receipt Sample** - Required
⚪ **Government ID #3** - Optional
⚪ **DTI Registration** - Optional
⚪ **SEC Registration** - Optional

---

## 📁 Files Modified

### **1. BusinessRegistrationController.php**
- Updated validation rules to require auction-style documents
- Added seller_type field (individual/business)
- Organized document uploads by category (selfie, government_ids, financial, business)
- Updated success messages to reflect seller type

### **2. Seller.php Model**
- Added `seller_type` to fillable fields

### **3. New Migration**
- `2026_03_01_100000_add_seller_type_to_sellers_table.php`
- Adds `seller_type` enum field (individual/business)

---

## 📋 Document Requirements Breakdown

### **Individual Seller Documents:**

| Document | Type | Required | Purpose |
|----------|------|----------|---------|
| Selfie | Image | Yes | Identity verification |
| Government ID #1 | PDF/Image | Yes | Primary identification |
| Government ID #2 | PDF/Image | Yes | Secondary identification |
| Bank Statement | PDF/Image | Yes | Financial verification |

**Accepted IDs:**
- Philippine Passport
- Driver's License
- UMID
- SSS ID
- PhilHealth ID
- Postal ID
- Voter's ID
- PRC ID
- Senior Citizen ID
- PWD ID

### **Business Seller Documents:**

| Document | Type | Required | Purpose |
|----------|------|----------|---------|
| Selfie | Image | Yes | Owner identity verification |
| Government ID #1 | PDF/Image | Yes | Owner primary identification |
| Government ID #2 | PDF/Image | Yes | Owner secondary identification |
| Bank Statement | PDF/Image | Yes | Business financial verification |
| Business Permit | PDF/Image | Yes | Legal business operation |
| BIR Certificate | PDF/Image | Yes | Tax registration |
| Official Receipt | PDF/Image | Yes | Business legitimacy proof |
| Government ID #3 | PDF/Image | Optional | Additional verification |
| DTI Registration | PDF/Image | Optional | Sole proprietorship proof |
| SEC Registration | PDF/Image | Optional | Corporation/partnership proof |

---

## 🎯 Benefits of This Change

### **1. Enhanced Security**
- Multiple ID verification prevents identity fraud
- Selfie requirement ensures document holder authenticity
- Bank statement verification confirms financial legitimacy

### **2. Consistency**
- ToyShop and Auction now have identical verification standards
- Unified admin review process
- Same document types across all seller platforms

### **3. Compliance**
- Meets Philippine e-commerce regulations
- BIR certificate ensures tax compliance
- Business permits verify legal operation

### **4. Fraud Prevention**
- Multiple document cross-verification
- Harder for scammers to fake multiple documents
- Protects buyers from fraudulent sellers

### **5. Professional Standards**
- Elevates platform credibility
- Attracts serious, legitimate sellers
- Builds buyer trust

---

## 🔧 Implementation Steps

### **1. Run Migration:**
```bash
php artisan migrate
```

### **2. Update Frontend Forms:**
The registration form needs to be updated to include:
- Seller type selection (Individual/Business)
- Selfie upload field
- Government ID #1 upload field
- Government ID #2 upload field
- Bank statement upload field
- Business-specific fields (conditional on seller type):
  - Business permit
  - BIR certificate
  - Official receipt sample
  - Optional: Government ID #3, DTI, SEC

### **3. Update Admin Review Process:**
Admins will now review:
- Selfie matches government IDs
- All government IDs are valid and not expired
- Bank statement is recent (within 3 months)
- Business documents are valid (for business sellers)
- All documents belong to the same person/entity

---

## 📊 Document Storage Structure

```
storage/app/public/seller_documents/{seller_id}/
├── selfie/
│   └── selfie.jpg
├── government_ids/
│   ├── government_id_1.pdf
│   ├── government_id_2.pdf
│   └── government_id_3.pdf (optional)
├── financial/
│   └── bank_statement.pdf
└── business/ (business sellers only)
    ├── business_permit.pdf
    ├── bir_certificate.pdf
    ├── official_receipt_sample.pdf
    ├── dti_registration.pdf (optional)
    └── sec_registration.pdf (optional)
```

---

## 🔍 Admin Verification Checklist

### **For All Sellers:**
- [ ] Selfie clearly shows face and ID
- [ ] Government ID #1 is valid and not expired
- [ ] Government ID #2 is valid and not expired
- [ ] Bank statement is recent (within 3 months)
- [ ] All documents belong to the same person
- [ ] Name matches across all documents
- [ ] Address is verifiable

### **For Business Sellers (Additional):**
- [ ] Business permit is valid and not expired
- [ ] BIR certificate shows active registration
- [ ] Official receipt sample is legitimate
- [ ] Business name matches across documents
- [ ] Owner/representative is authorized
- [ ] DTI/SEC registration matches business type (if provided)

---

## 🚨 Rejection Reasons

Common reasons for rejection:
1. **Blurry or unclear documents**
2. **Expired IDs or permits**
3. **Mismatched names across documents**
4. **Fake or altered documents**
5. **Bank statement older than 3 months**
6. **Business documents don't match business name**
7. **Selfie doesn't match ID photo**
8. **Missing required documents**

---

## 📝 Notes

1. **Existing Sellers:** Already approved sellers are not affected. This only applies to new registrations.

2. **Document Formats:** Accepted formats are PDF, JPG, JPEG, PNG (max 5MB per file).

3. **Processing Time:** Document verification may take 2-5 business days.

4. **Resubmission:** If rejected, sellers can resubmit corrected documents.

5. **Privacy:** All documents are stored securely and only accessible to admin reviewers.

6. **Retention:** Documents are retained for compliance purposes as per Philippine e-commerce laws.

---

## 🔗 Related Systems

This update aligns with:
- **Auction Seller Verification** - Identical requirements
- **KYC (Know Your Customer)** - Enhanced identity verification
- **Anti-Money Laundering** - Financial document verification
- **Philippine E-Commerce Law** - Compliance requirements

---

## 📞 Support

For questions about document requirements:
- Sellers can contact support@toyhaven.com
- Admin reviewers can refer to the verification guidelines
- Document templates available in the help center

---

**Updated:** March 1, 2026
**Version:** 2.0.0
**Status:** Active
