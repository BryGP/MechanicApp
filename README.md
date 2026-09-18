# MechanicApp

Workshop management system for auto-repair shops. Handles inventory tracking and service order management through a REST API backend and a Vue 3 single-page frontend.

---

## Architecture Overview

```
mechanic-app/
+-- backend/    # Laravel 12 REST API (PHP 8.2)
+-- frontend/   # Vue 3 SPA (Vite + Pinia + Vue Router)
```

The two layers communicate over HTTP — the frontend calls the backend API using the base URL configured in `frontend/.env`.

```
 [Browser]
     ¦
     +-? localhost:5173  ?  Vue 3 SPA (Vite dev server)
     ¦                         ¦
     ¦                         +-? fetch() calls
     ¦
     +-? localhost:8000  ?  Laravel API
                                 ¦
                                 +-? MySQL (XAMPP) — mechanic_app database
```

---

## Data Model

```
products
  id, name, sku, price, stock, min_stock

orders
  id, customer_name, vehicle, status, total
  status values: open | in_progress | done | delivered

order_items
  id, order_id (FK ? orders), product_id (FK ? products)
  qty, unit_price, subtotal
```

When an order is created, the backend automatically:
- Snapshots the product's current price into `unit_price`
- Calculates each line item's `subtotal`
- Decrements `products.stock` by the quantity ordered
- Sums all subtotals into `orders.total`

---

## API Endpoints

| Method | Endpoint              | Description                         |
|--------|-----------------------|-------------------------------------|
| GET    | /api/health           | Server health check                 |
| GET    | /api/products         | List all products (sorted by name)  |
| POST   | /api/products         | Create a new product                |
| PUT    | /api/products/{id}    | Update a product                    |
| DELETE | /api/products/{id}    | Delete a product                    |
| GET    | /api/orders           | List all orders with items          |
| POST   | /api/orders           | Create order + auto-decrement stock |
| GET    | /api/orders/{id}      | Get a single order with items       |
| PUT    | /api/orders/{id}      | Update order status/customer/vehicle|
| DELETE | /api/orders/{id}      | Delete an order and its items       |

---

## Running Locally

### Requirements
- PHP 8.2+ (XAMPP)
- Composer 2+
- Node.js 18+
- MySQL running in XAMPP

### Backend

```bash
cd backend
composer install
cp .env.example .env          # then set DB_* values for your MySQL
php artisan key:generate
php artisan migrate
php artisan db:seed           # loads sample products
php artisan serve             # runs on http://localhost:8000
```

### Frontend

```bash
cd frontend
npm install
# .env already configured: VITE_API_URL=http://localhost:8000/api
npm run dev                   # runs on http://localhost:5173
```

### Database (phpMyAdmin)
Open http://localhost/phpmyadmin ? database: **mechanic_app**

---

## Tech Stack

| Layer    | Technology          | Version |
|----------|---------------------|---------|
| Backend  | Laravel             | 12.x    |
| Backend  | Laravel Sanctum     | 4.x     |
| Database | MySQL / MariaDB     | 10.4    |
| Frontend | Vue 3 (Composition API) | 3.4 |
| Frontend | Vite                | 7.x     |
| Frontend | Pinia (state mgmt)  | 2.x     |
| Frontend | Vue Router          | 4.x     |
