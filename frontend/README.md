# MechanicApp — Frontend

Vue 3 single-page application for the MechanicApp workshop management system.
Built with Vite, Pinia for state management, and Vue Router for navigation.

---

## Directory Structure

```
frontend/
+-- src/
¦   +-- main.js                   # App entry point: registers Pinia + Router, mounts app
¦   +-- App.vue                   # Root layout: Sidebar + <router-view> content area
¦   +-- components/
¦   ¦   +-- Sidebar.vue           # Left nav: links to Products and Orders routes
¦   ¦   +-- ProductsView.vue      # Route /        — inventory list
¦   ¦   +-- OrdersView.vue        # Route /orders  — orders table + demo create
¦   ¦   +-- OrdersList.vue        # Alternative orders table (simpler, no items expanded)
¦   ¦   +-- Dashboard.vue         # Summary cards + recent orders table
¦   ¦   +-- ProductsCard.vue      # Reusable card component (title + description)
¦   +-- router/
¦   ¦   +-- index.js              # Route definitions: / and /orders
¦   +-- store/
¦       +-- index.js              # Pinia store: products[], orders[], fetch/create actions
+-- .env                          # VITE_API_URL=http://localhost:8000/api
+-- index.html                    # HTML shell with <div id="app">
+-- vite.config.js                # Vite config: Vue plugin, dev server port 5173
```

---

## State Management (Pinia Store)

The single store (`useStore`) holds:

| State      | Type    | Description                              |
|------------|---------|------------------------------------------|
| `products` | Array   | All inventory products from the API      |
| `orders`   | Array   | All service orders with their line items |

Available actions:

| Action           | HTTP Call              | Description                            |
|------------------|------------------------|----------------------------------------|
| `fetchProducts()`| GET /api/products      | Loads all products into state          |
| `fetchOrders()`  | GET /api/orders        | Loads all orders (with items) into state |
| `createOrder(p)` | POST /api/orders       | Creates a new order, then re-fetches   |

---

## Routes

| Path      | Component     | Description                      |
|-----------|---------------|----------------------------------|
| `/`       | ProductsView  | Inventory list                   |
| `/orders` | OrdersView    | Service orders + demo creation   |

---

## Environment Variables

| Variable        | Description                          |
|-----------------|--------------------------------------|
| `VITE_API_URL`  | Base URL for all backend API requests |

Default value: `http://localhost:8000/api`

---

## Dev Commands

```bash
npm install        # Install dependencies
npm run dev        # Start Vite dev server at http://localhost:5173
npm run build      # Build production bundle to dist/
npm run serve      # Preview production build locally
```
