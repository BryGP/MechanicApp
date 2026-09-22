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

### 6. Full-Stack Access Control and PIN Security
- Role Segregation: Non-administrative Operator mode for daily intake operations and elevated Administrator mode secured by cryptographic PIN verification.
- Destructive Action Protection: High-privilege actions (work order deletion, financial ledger removal) require modal PIN verification in the client interface and are validated over HTTP via the backend VerifyAdminPin middleware (X-Admin-Pin header) with 403 Forbidden enforcement.

---

## System Architecture

```
mechanic-app/
├── backend/                  # Laravel 11 REST API (PHP 8.2+)
│   ├── app/Http/Controllers  # Slim REST controllers delegating to transactions
│   ├── app/Http/Requests     # Dedicated Form Requests with anti-duplicate hooks
│   ├── app/Http/Resources    # Strongly typed API Resources (DTOs)
│   ├── app/Http/Middleware   # VerifyAdminPin authorization middleware
│   ├── app/Models            # Eloquent ORM entity models and relationships
│   └── database/             # Migrations with composite indexes and seeders
│
└── frontend/                 # Single Page Application (Vue 3 + Vite)
    ├── src/views/            # Primary view components (Dashboard, Products, Orders, Accounting, Reports)
    ├── src/components/       # Modular UI components, layout shells, and analytics widgets
    ├── src/composables/      # Shared state composables (useAuth, useConfirm, useToast)
    ├── src/store/            # Centralized Pinia state management store
    ├── src/utils/            # Centralized HTTP client (apiClient) and currency/date formatters
    └── src/assets/styles/    # Vanilla CSS design system with CSS custom properties
```

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

## API Reference

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
