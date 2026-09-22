# MechanicApp — Frontend SPA (Vue 3 + Vite)

Modern Single Page Application (SPA) for the MechanicApp workshop management system, engineered with an elevated dark theme, responsive components, CSS micro-animations, and centralized state management.

---

## Directory Structure

```
frontend/
├── src/
│   ├── main.js                  # Application entry point: initializes Vue 3, Pinia, and Router
│   ├── App.vue                  # Root layout shell: collapsible Sidebar, Header, and content outlet
│   ├── views/                   # Primary page components
│   │   ├── DashboardView.vue    # Executive KPIs, active repair orders, and low-stock alerts
│   │   ├── ProductsView.vue     # Inventory parts and labor service catalog
│   │   ├── OrdersView.vue       # Full-height repair order management with pagination
│   │   ├── AccountingView.vue   # Cash flow ledger, operating balance, and animated analytics
│   │   └── ReportsView.vue      # Analytical reporting engine with clipboard export
│   ├── components/
│   │   ├── layout/              # Sidebar.vue and AppHeader.vue (Operator vs Admin role toggle)
│   │   ├── ui/                  # Reusable UI widgets (StatCard, Badges, Modals, Toasts)
│   │   ├── charts/              # AnimatedBarChart.vue (Proportional animated progress gauges)
│   │   └── analytics/           # FinancialAnalyticsSection.vue (Operational metrics panel)
│   ├── composables/             # Composable hooks (useAuth, useConfirm, useToast)
│   ├── store/                   # Centralized Pinia state store
│   ├── utils/                   # apiClient.js (HTTP client) and format.js (Currency and date helpers)
│   └── assets/styles/           # main.css (Design tokens, layout resets, scrollbars, and keyframes)
├── index.html                   # HTML shell
└── vite.config.js               # Vite build configuration and dev server options
```

---

## Key Frontend Features

### 1. Centralized HTTP Client (src/utils/apiClient.js)
- Standardized fetch wrapper providing base URL resolution and automatic header generation (Content-Type and Accept).
- Transparently retrieves and attaches the X-Admin-Pin authorization header for protected actions.
- Intercepts and parses Laravel 422 validation responses, formatting field-level validation errors into user-friendly notifications.

### 2. Full-Height Layout and Dynamic Pagination
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

## Development Commands

```bash
# Start Vite development server with Hot Module Replacement (port 5173)
npm run dev

# Compile optimized production bundle to dist/
npm run build

# Preview production build locally
npm run serve
```
