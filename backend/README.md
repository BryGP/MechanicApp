# MechanicApp — Backend REST API (Laravel 11)

Decoupled backend service responsible for business logic execution, relational persistence, transactional integrity, and administrative authorization for the MechanicApp ERP system.

---

## Architectural Highlights

The backend implementation adheres to modern enterprise Laravel 11 patterns:

### 1. Dedicated Form Requests (app/Http/Requests)
Isolates request validation logic from controllers while providing integrity checks:
- StoreProductRequest and UpdateProductRequest: Strict validation rules distinguishing physical replacement parts from intangible services (enforces unique SKU, stock thresholds, and required pricing).
- StoreOrderRequest and UpdateOrderRequest: Validates customer metadata, vehicle strings, and item arrays. Includes an after-validation hook to prevent duplicate order submissions caused by rapid client-side retries.
- StoreExpenseRequest: Enforces category enumerations, payment method constraints, positive numeric amounts with a $1,000,000.00 MXN operational limit (preventing fat-finger errors), and duplicate submission prevention within short execution intervals.

### 2. API Resources and Data Transfer Objects (app/Http/Resources)
Data transformation layer guaranteeing consistent, typed JSON contracts:
- ProductResource: Enforces explicit numeric casting (stock as integer, price as float) and exposes computed flags such as stock_status and is_service.
- OrderResource and OrderItemResource: Formats nested line items and provides normalized currency floats for reliable client-side rendering.
- ExpenseResource: Standardizes expense serialization, date strings, and relational data.
- Configured JsonResource::withoutWrapping() in AppServiceProvider to deliver clean array and object structures matching frontend store expectations.

### 3. Slim Controllers and Services (app/Http/Controllers & app/Services)
- ProductController: Manages inventory and services catalog CRUD operations.
- OrderController: Coordinates atomic database transactions (DB::transaction) across orders, line items, and product stock decrements.
- ExpenseController: Manages ledger entries and cash flow records.
- ReportController: Dispatches predefined management SQL queries.
- AdminPinController & AdminPinService: Centralized persistent security credential management. Supports disk-based storage (storage/app/admin_pin.txt), in-memory cache, credential rotation (/api/admin/pin/change), remote verification (/api/admin/pin/verify), and automatic client reconciliation (/api/admin/pin/sync).

### 4. Full-Stack Access Control: Admin PIN Middleware (app/Http/Middleware)
- VerifyAdminPin (registered as 'admin.pin' alias): Intercepts incoming HTTP requests on destructive endpoints (DELETE /api/orders/{id} and DELETE /api/expenses/{id}). Dynamically checks the X-Admin-Pin header against the active persistent PIN via AdminPinService::verify(), rejecting unauthorized requests with HTTP 403 Forbidden.
- Automated Test Suite (tests/Feature/AdminPinSecurityTest.php): Comprehensive feature tests confirming 403 Forbidden rejections on missing/invalid PINs, verification endpoints, credential rotation, and successful HTTP 200 execution when authorized.

---

## Directory Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # ProductController, OrderController, ExpenseController, ReportController, AdminPinController
│   │   ├── Middleware/        # VerifyAdminPin (Admin PIN header inspection)
│   │   ├── Requests/          # Dedicated Form Requests with anti-duplicate validation
│   │   └── Resources/         # Typed API Resources (DTOs)
│   ├── Models/                # Product, Order, OrderItem, Expense, ReportTemplate, User
│   ├── Providers/             # AppServiceProvider (JsonResource global configuration)
│   └── Services/              # AdminPinService (Persistent PIN storage, rotation, and verification)
├── bootstrap/                 # Application bootstrap and middleware alias registrations
├── database/
│   ├── migrations/            # Table schemas and composite performance indexes
│   └── seeders/               # Database seeders and monthly operation simulation scripts
├── routes/
│   └── api.php                # REST API routes and middleware definitions
└── tests/
    └── Feature/               # Automated integration tests (AdminPinSecurityTest)
```

---

## Essential Commands

```bash
# Start backend API development server (port 8000)
php artisan serve

# Execute database migrations
php artisan migrate

# Run feature and unit test suites
php artisan test

# Launch interactive tinker REPL
php artisan tinker

# Display all registered API endpoints
php artisan route:list --path=api
```
