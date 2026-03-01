# Registration Flow Diagram

## User Journey Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    /seller/register                              │
│                  (Registration Index)                            │
│                                                                  │
│  ┌────────────────────────────┐  ┌─────────────────────────┐  │
│  │  Local Business Toyshop    │  │ Verified Trusted Toyshop│  │
│  │  - Basic verification      │  │ - Enhanced verification │  │
│  │  - 3 documents required    │  │ - 6 documents required  │  │
│  │  - Quick approval          │  │ - Priority features     │  │
│  └────────────┬───────────────┘  └──────────┬──────────────┘  │
└───────────────┼──────────────────────────────┼─────────────────┘
                │                              │
                ▼                              ▼
┌───────────────────────────────────────────────────────────────────┐
│              /seller/register?type=basic or verified              │
│                     (Registration Form)                           │
└───────────────────────────────────────────────────────────────────┘
                                │
                                ▼
        ┌───────────────────────────────────────┐
        │     1. Business Information           │
        │     ✓ Business Name (required)        │
        │     ✓ Description (required, justified)│
        │     ✓ Phone (+63 format)              │
        │     ✓ Email                           │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     2. Business Address               │
        │     ✓ Street Address (combined)       │
        │     ✓ Region ──→ Province             │
        │              └──→ City                │
        │                   └──→ Barangay       │
        │     ✓ Postal Code (4 digits)          │
        │     [Uses PSGC Cloud API]             │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     3. Required Documents             │
        │     [All with Preview System]         │
        │                                       │
        │     For ALL:                          │
        │     ✓ Primary ID                      │
        │     ✓ Facial Verification             │
        │     ✓ Bank Statement                  │
        │                                       │
        │     For VERIFIED only:                │
        │     ✓ Business Permit                 │
        │     ✓ BIR Certificate                 │
        │     ✓ Product Sample                  │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     4. Toy Categories                 │
        │     [Card-based Selection]            │
        │     ✓ Select 1-3 categories           │
        │     ✓ Icons + Descriptions            │
        │     ✓ Live counter                    │
        │     ✓ Auto-disable at 3               │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     5. Social Media Links (Optional)  │
        │     ✓ Facebook                        │
        │     ✓ Instagram                       │
        │     ✓ TikTok                          │
        │     ✓ Website                         │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     6. Submit Registration            │
        │     [Validation + Storage]            │
        └───────────────┬───────────────────────┘
                        ▼
        ┌───────────────────────────────────────┐
        │     Success!                          │
        │     → Redirect to Seller Dashboard    │
        │     → Show success message            │
        │     → Status: Pending Approval        │
        └───────────────────────────────────────┘
```

---

## Data Flow Diagram

```
┌──────────────┐
│   Browser    │
└──────┬───────┘
       │
       │ 1. User selects registration type
       ▼
┌──────────────────────────────────────────┐
│  RegistrationController::show()          │
│  - Check if already seller               │
│  - Get user's default address            │
│  - Pre-fill form data                    │
│  - Load active categories                │
└──────┬───────────────────────────────────┘
       │
       │ 2. Render form with pre-filled data
       ▼
┌──────────────────────────────────────────┐
│  seller/registration/form.blade.php      │
│  - Display form fields                   │
│  - Load JavaScript for interactions      │
└──────┬───────────────────────────────────┘
       │
       │ 3. User interacts with form
       ▼
┌──────────────────────────────────────────┐
│  JavaScript Events                       │
│  ┌────────────────────────────────────┐  │
│  │ Address API Calls                  │  │
│  │ Region → https://psgc.cloud/api    │  │
│  │ Province → API call                │  │
│  │ City → API call                    │  │
│  │ Barangay → API call                │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │ Document Upload                    │  │
│  │ - FileReader API for preview       │  │
│  │ - Validate size & type             │  │
│  │ - Show thumbnail                   │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │ Category Selection                 │  │
│  │ - Count selections                 │  │
│  │ - Enforce 1-3 limit                │  │
│  │ - Update UI                        │  │
│  └────────────────────────────────────┘  │
└──────┬───────────────────────────────────┘
       │
       │ 4. User submits form
       ▼
┌──────────────────────────────────────────┐
│  RegistrationController::store()         │
│  ┌────────────────────────────────────┐  │
│  │ 1. Validate Input                  │  │
│  │    - Business info                 │  │
│  │    - Address fields                │  │
│  │    - Documents                     │  │
│  │    - Categories (1-3)              │  │
│  │    - Social media URLs             │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │ 2. Create Seller Record            │  │
│  │    - Save to database              │  │
│  │    - Generate business slug        │  │
│  │    - Set verification_status       │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │ 3. Store Documents                 │  │
│  │    - Upload to storage/            │  │
│  │    - Create SellerDocument records │  │
│  │    - Set status: pending           │  │
│  └────────────────────────────────────┘  │
│  ┌────────────────────────────────────┐  │
│  │ 4. Update User Role                │  │
│  │    - Set role: 'seller'            │  │
│  └────────────────────────────────────┘  │
└──────┬───────────────────────────────────┘
       │
       │ 5. Redirect with success message
       ▼
┌──────────────────────────────────────────┐
│  Seller Dashboard                        │
│  - Show success message                  │
│  - Display pending verification status   │
└──────────────────────────────────────────┘
```

---

## Database Schema Updates

```
┌─────────────────────────────────────────┐
│           sellers table                 │
├─────────────────────────────────────────┤
│ Existing Fields:                        │
│ - id                                    │
│ - user_id                               │
│ - business_name                         │
│ - business_slug                         │
│ - description                           │
│ - phone                                 │
│ - email                                 │
│ - address                               │
│ - city                                  │
│ - province                              │
│ - postal_code                           │
│ - toy_category_ids (JSON)               │
│ - verification_status                   │
│ - is_verified_shop                      │
│ - ...                                   │
├─────────────────────────────────────────┤
│ NEW Fields (Added):                     │
│ + region          VARCHAR(100)          │
│ + barangay        VARCHAR(100)          │
│ + facebook_url    VARCHAR(255)          │
│ + instagram_url   VARCHAR(255)          │
│ + tiktok_url      VARCHAR(255)          │
│ + website_url     VARCHAR(255)          │
└─────────────────────────────────────────┘
```

---

## Document Storage Structure

```
storage/app/public/
└── seller_documents/
    └── {seller_id}/
        ├── id_document.pdf
        ├── facial_verification.jpg
        ├── bank_statement.pdf
        ├── business_permit.pdf (verified only)
        ├── bir_certificate.pdf (verified only)
        └── product_sample.jpg (verified only)
```

---

## API Integration Flow

```
┌──────────────┐
│   Browser    │
└──────┬───────┘
       │
       │ 1. Page loads
       ▼
┌─────────────────────────────────────────┐
│  JavaScript: Load Regions               │
└──────┬──────────────────────────────────┘
       │
       │ GET https://psgc.cloud/api/regions
       ▼
┌─────────────────────────────────────────┐
│  PSGC Cloud API                         │
│  Returns: [                             │
│    {code: "010000000", name: "Region I"}│
│    {code: "020000000", name: "Region II"}│
│    ...                                  │
│  ]                                      │
└──────┬──────────────────────────────────┘
       │
       │ 2. User selects Region
       ▼
┌─────────────────────────────────────────┐
│  JavaScript: Load Provinces             │
└──────┬──────────────────────────────────┘
       │
       │ GET /api/regions/{code}/provinces
       ▼
┌─────────────────────────────────────────┐
│  PSGC Cloud API                         │
│  Returns: [                             │
│    {code: "012800000", name: "Ilocos Norte"}│
│    {code: "012900000", name: "Ilocos Sur"}│
│    ...                                  │
│  ]                                      │
└──────┬──────────────────────────────────┘
       │
       │ 3. User selects Province
       ▼
┌─────────────────────────────────────────┐
│  JavaScript: Load Cities                │
└──────┬──────────────────────────────────┘
       │
       │ GET /api/provinces/{code}/cities-municipalities
       ▼
┌─────────────────────────────────────────┐
│  PSGC Cloud API                         │
│  Returns: [                             │
│    {code: "012801000", name: "Batac City"}│
│    {code: "012802000", name: "Laoag City"}│
│    ...                                  │
│  ]                                      │
└──────┬──────────────────────────────────┘
       │
       │ 4. User selects City
       ▼
┌─────────────────────────────────────────┐
│  JavaScript: Load Barangays             │
└──────┬──────────────────────────────────┘
       │
       │ GET /api/cities-municipalities/{code}/barangays
       ▼
┌─────────────────────────────────────────┐
│  PSGC Cloud API                         │
│  Returns: [                             │
│    {name: "Barangay 1"}                 │
│    {name: "Barangay 2"}                 │
│    ...                                  │
│  ]                                      │
└──────┬──────────────────────────────────┘
       │
       │ 5. Populate dropdown
       ▼
┌─────────────────────────────────────────┐
│  User can now select Barangay           │
└─────────────────────────────────────────┘
```

---

## Validation Flow

```
┌──────────────────────────────────────────┐
│  Form Submission                         │
└──────┬───────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────────┐
│  Client-Side Validation (JavaScript)     │
│  ✓ Category count (1-3)                  │
│  ✓ File size (<5MB)                      │
│  ✓ File type (pdf, jpg, jpeg, png)       │
│  ✓ Postal code (4 digits)                │
└──────┬───────────────────────────────────┘
       │
       │ If valid
       ▼
┌──────────────────────────────────────────┐
│  Server-Side Validation (Laravel)        │
│  ✓ business_name (required, max:255)     │
│  ✓ description (required, max:2000)      │
│  ✓ phone (required, +63 format)          │
│  ✓ email (required, email format)        │
│  ✓ address (required, max:500)           │
│  ✓ region (required, max:100)            │
│  ✓ province (required, max:100)          │
│  ✓ city (required, max:100)              │
│  ✓ barangay (required, max:100)          │
│  ✓ postal_code (required, 4 digits)      │
│  ✓ toy_category_ids (array, 1-3 items)   │
│  ✓ documents (required, valid files)     │
│  ✓ social_urls (optional, valid URLs)    │
└──────┬───────────────────────────────────┘
       │
       │ If valid
       ▼
┌──────────────────────────────────────────┐
│  Data Storage                            │
│  ✓ Create seller record                  │
│  ✓ Upload documents                      │
│  ✓ Update user role                      │
└──────┬───────────────────────────────────┘
       │
       │ Success
       ▼
┌──────────────────────────────────────────┐
│  Redirect to Dashboard                   │
│  Show success message                    │
└──────────────────────────────────────────┘
```

---

## Component Interaction Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                             │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐ │
│  │  Form Fields   │  │   JavaScript   │  │  PSGC API    │ │
│  │  - Inputs      │◄─┤  - Validation  │◄─┤  - Regions   │ │
│  │  - Dropdowns   │  │  - API Calls   │  │  - Provinces │ │
│  │  - File Upload │  │  - Preview     │  │  - Cities    │ │
│  │  - Categories  │  │  - Counter     │  │  - Barangays │ │
│  └────────┬───────┘  └────────┬───────┘  └──────────────┘ │
└───────────┼──────────────────┼─────────────────────────────┘
            │                  │
            │ Form Submit      │ AJAX Requests
            ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                        Backend                              │
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────┐ │
│  │  Controller    │  │     Model      │  │   Storage    │ │
│  │  - Validate    │─►│  - Seller      │─►│  - Documents │ │
│  │  - Store       │  │  - Documents   │  │  - Public    │ │
│  │  - Redirect    │  │  - User        │  │              │ │
│  └────────┬───────┘  └────────┬───────┘  └──────────────┘ │
└───────────┼──────────────────┼─────────────────────────────┘
            │                  │
            │                  ▼
            │         ┌────────────────┐
            │         │    Database    │
            │         │  - sellers     │
            │         │  - documents   │
            │         │  - users       │
            │         └────────────────┘
            ▼
┌─────────────────────────────────────────────────────────────┐
│                     Response                                │
│  - Redirect to dashboard                                    │
│  - Success message                                          │
│  - Session data                                             │
└─────────────────────────────────────────────────────────────┘
```

---

This diagram provides a complete visual overview of how the seller registration system works, from user interaction to data storage.
