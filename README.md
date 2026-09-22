# MechanicApp — Workshop Management & ERP System

Enterprise resource planning (ERP) and operational management system designed for automotive service centers specializing in internal combustion engine (ICE) vehicles, electric vehicles (EV), and classic collector automobiles. Built on a decoupled full-stack architecture featuring a robust Laravel 11 REST API backend and a responsive Vue 3 Single Page Application (SPA).

---

## Core Modules and Functional Capabilities

### 1. Dashboard and Executive Overview
- Real-Time KPIs: Instant metrics for warehouse catalog items, critical stock alerts, active repair orders, and cumulative service revenue.
- Operational Visibility: Live tracking of latest repair orders and immediate identification of low-stock inventory.

### 2. Service and Work Order Lifecycle
- Comprehensive Workflow: Structured status transitions: Open -> In Progress -> Done -> Delivered.
- Multi-Item Catalog Picker: Composite repair orders combining physical replacement parts and technical labor/services with real-time subtotaling and atomic inventory decrementing.
- Inspection Modal and Printable Ticket: Deep inspection view detailing vehicle information, diagnostic notes, itemized billing, and browser-native ticket/invoice generation via window.print().
- Responsive Full-Height Layout: Viewport-filling data table with internal vertical scroll, sticky column headers, and dynamic pagination selector (10, 20, 50, 100 entries per page).

### 3. Inventory and Services Catalog
- Dual Catalog Architecture: Clear architectural distinction between physical warehouse replacement parts (with tracked stock levels and reorder thresholds) and intangible technical labor/services (is_service flag).
- Inventory Health Tracking: Categorized stock status indicators (Optimal, Low Stock, Out of Stock) configured to exclude services, preventing false inventory warnings.
- Uniqueness and Data Integrity: SKU and product name uniqueness constraints enforced at both request validation and database index levels.

### 4. Accounting and Operational Finance
- Operational Expense Ledger: Structured recording of workshop expenditures categorized into Payroll, Parts/Supplies, Tools/Equipment, Rent, Utilities, and Other Expenses, complete with payment methods and reference identifiers.
- Modernized Expense Intake Modal: Two-panel workflow featuring custom SVG vector iconography, strict numeric input sanitization (blocking non-numeric signs and scientific notation), live cash flow debit preview banner, and an ISO 9001:2015 fat-finger safeguard capping individual entries at $1,000,000.00 MXN.
- Fiscal Compliance Guidance: Contextual warnings alerting operators that cash payments exceeding $2,000.00 MXN are not tax-deductible under Mexican SAT regulations (LISR Art. 27 Fracc. III).
- Live Cash Flow Engine: Real-time calculation of Gross Service Revenue, Operational Expenses, Net Operating Profit, and Operating Margin percentage.
- Animated Analytics Suite:
  - Expense Category Breakdown: Proportional horizontal bar charts with fluid cubic-bezier CSS width transitions.
  - Operating Balance and Absorption: Comparative visualization of revenue versus overhead, accompanied by capital return metrics per currency unit spent.
  - Vehicle and Throughput Metrics: Tracking of serviced vehicles, average invoice ticket per vehicle, and high-frequency service models.
- Clean Localization: Native date formatting in Spanish (e.g., 21 sep 2026) eliminating raw ISO timestamp strings.

### 5. Management Reporting and Business Intelligence
- Structured SQL Analytics: Pre-configured analytical reporting catalog divided into four management domains: Inventory, Sales, Operations, and Finance.
- Spreadsheet Export: Direct tabular clipboard copying formatted for immediate integration into Excel or Google Sheets.
- Executive Performance Summaries: Automated valuation of dead inventory, critical reorder schedules, and financial margin analysis.

### 6. Full-Stack Access Control and Dynamic PIN Security
- Role Segregation: Non-administrative Operator mode for daily intake operations and elevated Administrator mode secured by numeric PIN authorization.
- Persistent Multi-Tier PIN Architecture: Centralized AdminPinService managing persistent storage on disk (storage/app/admin_pin.txt), in-memory cache, and fallback defaults.
- Real-Time Credential Rotation & Reconciliation: Dedicated REST endpoints (/api/admin/pin/change, /api/admin/pin/sync, /api/admin/pin/verify) providing seamless client-server credential synchronization across browser sessions.
- Destructive Action Protection: High-privilege actions (work order deletion, financial ledger removal) require modal authorization in the client interface and are validated over HTTP via the backend VerifyAdminPin middleware (X-Admin-Pin header) with 403 Forbidden enforcement.

---

## Full-Stack System Architecture

```
mechanic-app/
├── backend/                               # Laravel 11 REST API Service (PHP 8.2+)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/               # Slim REST controllers (Product, Order, Expense, Report, AdminPin)
│   │   │   ├── Middleware/                # VerifyAdminPin (Dynamic X-Admin-Pin header inspection)
│   │   │   ├── Requests/                  # Dedicated Form Requests with anti-duplicate validation hooks
│   │   │   └── Resources/                 # Strongly typed API Resources (DTOs with JsonResource::withoutWrapping)
│   │   ├── Models/                        # Eloquent ORM entity models (Product, Order, OrderItem, Expense, ReportTemplate, User)
│   │   ├── Providers/                     # AppServiceProvider (Global JSON formatting and API services)
│   │   └── Services/                      # AdminPinService (Persistent file and cache credential management)
│   ├── bootstrap/                         # Application bootstrap and middleware alias registrations
│   ├── database/
│   │   ├── migrations/                    # Table schemas and composite performance indexes
│   │   └── seeders/                       # Seeders and monthly operational simulation scripts
│   ├── routes/
│   │   └── api.php                        # Central REST API routing table
│   └── tests/
│       └── Feature/                       # Automated integration test suite (AdminPinSecurityTest)
│
└── frontend/                              # Single Page Application (Vue 3 + Vite)
    ├── src/
    │   ├── main.js                        # Client entry point: initializes Vue 3, Pinia, and Vue Router
    │   ├── App.vue                        # Root application layout shell (Sidebar, AppHeader, View outlet)
    │   ├── views/                         # Primary application page views
    │   │   ├── DashboardView.vue          # Executive KPIs, active work orders, and low-stock alerts
    │   │   ├── ProductsView.vue           # Warehouse inventory and labor service catalog
    │   │   ├── OrdersView.vue             # Full-height repair order management with dynamic pagination
    │   │   ├── AccountingView.vue         # Financial ledger, cash flow engine, and animated analytics
    │   │   └── ReportsView.vue            # SQL analytical reporting engine with spreadsheet clipboard export
    │   ├── components/
    │   │   ├── layout/                    # Sidebar.vue and AppHeader.vue (Role authentication and PIN modal)
    │   │   ├── ui/                        # Reusable UI widgets (StatCard, Badges, Modals, ConfirmModal, Toasts)
    │   │   ├── charts/                    # AnimatedBarChart.vue (Cubic-bezier animated progress gauges)
    │   │   └── analytics/                 # FinancialAnalyticsSection.vue (Operational metrics breakdown)
    │   ├── composables/                   # Shared composable hooks (useAuth, useConfirm, useToast)
    │   ├── store/                         # Centralized Pinia reactive application store
    │   ├── utils/                         # apiClient.js (Centralized HTTP client) and format.js (Currency/date formatters)
    │   └── assets/styles/                 # main.css (Design tokens, layout resets, scrollbars, and keyframes)
    ├── index.html                         # Client HTML entry document
    └── vite.config.js                     # Vite build configuration and development server options
```

---

## Backend Engineering & Architectural Patterns

### 1. Dedicated Form Requests (app/Http/Requests)
Isolates request validation logic from controllers while providing comprehensive integrity checks:
- StoreProductRequest & UpdateProductRequest: Strict validation rules distinguishing physical replacement parts from intangible services (enforces unique SKU, stock thresholds, and required pricing).
- StoreOrderRequest & UpdateOrderRequest: Validates customer metadata, vehicle strings, and item arrays. Includes an after-validation hook to prevent duplicate order submissions caused by rapid client-side retries.
- StoreExpenseRequest: Enforces category enumerations, payment method constraints, positive numeric amounts with a $1,000,000.00 MXN operational limit (preventing fat-finger errors), and duplicate submission prevention within short execution intervals.

### 2. API Resources & Data Transfer Objects (app/Http/Resources)
Data transformation layer guaranteeing consistent, typed JSON contracts:
- ProductResource: Enforces explicit numeric casting (stock as integer, price as float) and exposes computed flags such as stock_status and is_service.
- OrderResource & OrderItemResource: Formats nested line items and provides normalized currency floats for reliable client-side rendering.
- ExpenseResource: Standardizes expense serialization, date strings, and relational data.
- Configured JsonResource::withoutWrapping() in AppServiceProvider to deliver clean array and object structures matching frontend store expectations.

### 3. Slim Controllers & Services (app/Http/Controllers & app/Services)
- ProductController: Manages inventory and services catalog CRUD operations.
- OrderController: Coordinates atomic database transactions (DB::transaction) across orders, line items, and product stock decrements.
- ExpenseController: Manages ledger entries and cash flow records.
- ReportController: Dispatches predefined management SQL queries.
- AdminPinController & AdminPinService: Centralized persistent security credential management. Supports disk-based storage (storage/app/admin_pin.txt), in-memory cache, credential rotation (/api/admin/pin/change), remote verification (/api/admin/pin/verify), and automatic client reconciliation (/api/admin/pin/sync).

### 4. Dynamic Security Middleware (app/Http/Middleware)
- VerifyAdminPin (registered as 'admin.pin' alias): Intercepts incoming HTTP requests on destructive endpoints (DELETE /api/orders/{id} and DELETE /api/expenses/{id}). Dynamically checks the X-Admin-Pin header against the active persistent PIN via AdminPinService::verify(), rejecting unauthorized requests with HTTP 403 Forbidden.
- Automated Test Suite (tests/Feature/AdminPinSecurityTest.php): Comprehensive feature tests confirming 403 Forbidden rejections on missing/invalid PINs, verification endpoints, credential rotation, and successful HTTP 200 execution when authorized.

---

## Frontend Engineering & UX Patterns

### 1. Centralized HTTP Client (src/utils/apiClient.js)
- Standardized fetch wrapper providing base URL resolution and automatic header generation (Content-Type and Accept).
- Transparently retrieves and attaches the X-Admin-Pin authorization header for protected actions.
- Intercepts and parses Laravel 422 validation responses, formatting field-level validation errors into user-friendly notifications.

### 2. Full-Height Layout and Dynamic Viewport Pagination
- Page Size Selector: Configurable records per view (10, 20, 50, 100 items per page).
- Viewport Optimization: Data tables expand to occupy full viewport height (calc(100vh - var(--header-h) - 4rem)), eliminating dead visual space while docking the pagination bar at the bottom.
- Sticky Table Headers: Column headers remain fixed at the top during vertical scrolling.
- Real-Time Indicators: Accurate range tracking (e.g., "Showing 1 to 10 of 75 orders") with automatic page resets upon filter or search updates.

### 3. Animated Analytics Suite (src/components/charts/AnimatedBarChart.vue)
- Proportional progress bar gauges featuring smooth cubic-bezier CSS animations expanding from 0% on mount.
- Real-time aggregation of operational expenses by category, cost absorption rates, and vehicle repair volume.

### 4. Role Authentication, Dynamic PIN Security, and Confirmation Dialogs
- Header-based role indicator toggling between standard Operator and elevated Administrator privileges.
- Client-Server PIN Reconciliation: useAuth automatically syncs custom client PINs with the Laravel backend upon startup via /api/admin/pin/sync, and communicates credential changes via /api/admin/pin/change.
- Centralized asynchronous confirmation modal (ConfirmModal.vue) with masked PIN keypad, auto-clearing input on failure, and shake animation for destructive operations.

### 5. Modernized Accounting and Expense Modal (src/views/AccountingView.vue)
- Two-Panel Modal Architecture: Structured grouping dividing expense categorization from disbursement details.
- Strict Numeric Input Sanitization: Filters keyboard input, clipboard pasting, and text changes to disallow symbols, signs (+/-), and scientific notation (e/E), enforcing valid numeric decimals up to two decimal places.
- Operational Ceiling and Compliance Safeguards: Hard ceiling of $1,000,000.00 MXN preventing catastrophic fat-finger entries (ISO 9001:2015), accompanied by contextual SAT alerts (LISR Art. 27 Fracc. III) for non-deductible cash outlays exceeding $2,000.00 MXN.
- Real-Time Impact Banner: Dynamic preview reflecting cash flow debit and operational category impact prior to persistence.

---

## Relational Data Model

- products: id, name, sku, price, stock, min_stock, is_service, description.
- orders: id, customer_name, vehicle, notes, status, total, created_at, updated_at.
- order_items: id, order_id (FK), product_id (FK), quantity, unit_price, subtotal.
- expenses: id, concept, category, amount, payment_method, reference, expense_date.
- report_templates: id, name, description, category, sql_query.

### High-Performance Database Indexes
Composite indexes implemented to optimize high-frequency filtering and analytical queries:
- orders: (status, created_at)
- products: (is_service, stock, min_stock)
- expenses: (expense_date, category)

---

## Complete REST API Reference

| Module | Method | Endpoint | Description | Security |
| :--- | :--- | :--- | :--- | :--- |
| Health | GET | /api/health | Service availability check | Public |
| Products | GET | /api/products | List inventory and service items | Public |
| | POST | /api/products | Create product via StoreProductRequest | Public |
| | PUT | /api/products/{id} | Update product via UpdateProductRequest | Public |
| | DELETE | /api/products/{id} | Remove catalog item | Public |
| Orders | GET | /api/orders | Retrieve orders with relational line items | Public |
| | POST | /api/orders | Atomic order creation and inventory decrement | Public |
| | GET | /api/orders/{id} | Retrieve detailed order inspection data | Public |
| | PUT | /api/orders/{id} | Update status, customer, or vehicle data | Public |
| | DELETE | /api/orders/{id} | Permanent work order deletion | Protected (Requires X-Admin-Pin) |
| Expenses | GET | /api/expenses | Retrieve operational expenses ledger | Public |
| | POST | /api/expenses | Record expense via StoreExpenseRequest | Public |
| | DELETE | /api/expenses/{id} | Remove accounting expense entry | Protected (Requires X-Admin-Pin) |
| Admin PIN | POST | /api/admin/pin/verify | Validate administrator credentials | Public |
| | POST | /api/admin/pin/change | Securely rotate administrator security PIN | Public |
| | POST | /api/admin/pin/sync | Client-server credential reconciliation | Public |
| Reports | GET | /api/reports | List analytical report templates | Public |
| | POST | /api/reports/{id}/run | Execute dynamic SQL business query | Public |

---

## Technology Stack

| Domain | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| Backend | Laravel | 11.x | REST API application framework |
| Runtime | PHP | 8.2+ | Server-side execution and transaction processing |
| Database | MySQL / MariaDB | 10.4+ | Relational data persistence with composite indexing |
| Frontend | Vue 3 | 3.4+ | Composition API with Single File Components (.vue) |
| Tooling | Vite | 7.x | Fast build tool and dev server with Hot Module Replacement |
| State | Pinia | 2.1+ | Centralized reactive application store |
| Routing | Vue Router | 4.2+ | Client-side routing with HTML5 history mode |
| Styling | Vanilla CSS | Modern | Design tokens, responsive containers, and keyframe animations |

---

## Operational and Development Commands

### Backend Commands (Laravel 11)
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

### Frontend Commands (Vue 3 + Vite)
```bash
# Start Vite development server with Hot Module Replacement (port 5173)
npm run dev

# Compile optimized production bundle to dist/
npm run build

# Preview production build locally
npm run serve
```
