# MechanicApp — Backend

Laravel 12 REST API for the MechanicApp workshop management system.

---

## Directory Structure

```
backend/
+-- app/
¦   +-- Http/Controllers/
¦   ¦   +-- ProductController.php   # CRUD for inventory products
¦   ¦   +-- OrderController.php     # CRUD for service orders (with stock logic)
¦   +-- Models/
¦       +-- Product.php             # Inventory item model
¦       +-- Order.php               # Service order model (has many OrderItems)
¦       +-- OrderItem.php           # Line item model (belongs to Order + Product)
¦       +-- User.php                # Auth user model (Sanctum-ready)
+-- database/
¦   +-- migrations/                 # Schema definitions (run in order)
¦   +-- seeders/
¦       +-- DatabaseSeeder.php      # Master seeder entry point
¦       +-- ProductSeeder.php       # Sample workshop products
+-- routes/
¦   +-- api.php                     # All API endpoints (/api/*)
¦   +-- web.php                     # Web routes (unused in API-only mode)
+-- config/
    +-- cors.php                    # CORS: allows all origins (open for dev)
    +-- sanctum.php                 # API token authentication config
```

---

## Key Commands

```bash
# Install dependencies
composer install

# Generate application encryption key
php artisan key:generate

# Run all database migrations
php artisan migrate

# Seed with sample data (3 products)
php artisan db:seed

# Start the development server (port 8000)
php artisan serve

# Open interactive PHP REPL (query models directly)
php artisan tinker

# List all registered API routes
php artisan route:list --path=api
```

---

## Environment Variables (.env)

| Variable        | Description                        | Default           |
|-----------------|------------------------------------|-------------------|
| APP_KEY         | Laravel encryption key (auto-gen)  | —                 |
| DB_CONNECTION   | Database driver                    | mysql             |
| DB_HOST         | Database host                      | 127.0.0.1         |
| DB_PORT         | Database port                      | 3306              |
| DB_DATABASE     | Database name                      | mechanic_app      |
| DB_USERNAME     | Database user                      | root              |
| DB_PASSWORD     | Database password                  | (empty in XAMPP)  |

---

## Business Logic Notes

### Order Creation (POST /api/orders)
The entire order creation runs inside a **database transaction**:
1. Create the parent `Order` with `status = open` and `total = 0`
2. For each item: fetch product ? snapshot price ? decrement stock ? create `OrderItem`
3. Update `Order.total` with the sum of all subtotals
4. If any step fails, the entire transaction is rolled back

### Stock Management
- Stock is decremented automatically when an order is created
- There is no automatic stock restoration on order deletion (handle manually)
- Products with `stock < min_stock` are candidates for reorder alerts (frontend can check this)

### CORS
Currently configured to allow all origins (`*`) in `config/cors.php`.
Restrict this to your frontend domain in production.
