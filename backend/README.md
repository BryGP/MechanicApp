# MechanicApp — Backend REST API (Laravel 11)

Servicio backend desacoplado que gestiona la lógica de negocio, persistencia relacional, integridad transaccional y seguridad administrativa del ERP para talleres mecánicos.

---

## 🏛️ Arquitectura y Componentes Clave

El backend sigue las mejores prácticas de la arquitectura moderna en Laravel 11:

### 1. Form Requests Dedicados (`app/Http/Requests`)
Desacoplan la validación de los controladores y previenen inconsistencias:
- **`StoreProductRequest` / `UpdateProductRequest`:** Validación estricta para refacciones físicas vs servicios de mano de obra (SKU único, stock mínimo, precio requerido).
- **`StoreOrderRequest` / `UpdateOrderRequest`:** Validación de cliente, vehículo, partidas de items y gancho `withValidator()` para prevenir órdenes duplicadas enviadas accidentalmente en ráfaga.
- **`StoreExpenseRequest`:** Validación de concepto, categoría contable, importe, método de pago y prevención de egresos duplicados.

### 2. API Resources / DTOs (`app/Http/Resources`)
Capa de transformación que asegura contratos JSON estrictos y tipados:
- **`ProductResource`:** Formateo tipado numérico explícito (`stock`, `price`, `min_stock`) y estados computados (`stock_status`, `is_service`).
- **`OrderResource` & `OrderItemResource`:** Serialización relacional de órdenes con sus partidas anidadas e importes flotantes formateados.
- **`ExpenseResource`:** Serialización tipada de partidas contables de egreso.
- `JsonResource::withoutWrapping()` configurado en `AppServiceProvider` para entregar respuestas directas y limpias sin empaquetado innecesario.

### 3. Controladores Esbeltos (`app/Http/Controllers`)
- **`ProductController`:** CRUD de catálogo de refacciones y servicios de mano de obra.
- **`OrderController`:** Orquestación transaccional (`DB::transaction`) para crear órdenes, calcular subtotales y descontar inventario de forma atómica.
- **`ExpenseController`:** Control del libro diario contable y flujo de caja operativo.
- **`ReportController`:** Motor de ejecución de consultas SQL analíticas.

### 4. Seguridad Full-Stack: Middleware de PIN (`app/Http/Middleware`)
- **`VerifyAdminPin` (alias `'admin.pin'`):** Inspecciona la cabecera HTTP `X-Admin-Pin` en operaciones de alto valor destructivo (`DELETE /api/orders/{id}` y `DELETE /api/expenses/{id}`). Rechaza con código **`403 Forbidden`** ante cualquier intento no autorizado.
- **Pruebas Automatizadas (`tests/Feature/AdminPinSecurityTest.php`):** Suite completa con aserciones en memoria que validan el rechazo 403 y la autorización correcta 200.

---

## 📂 Estructura de Directorios

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # ProductController, OrderController, ExpenseController, ReportController
│   │   ├── Middleware/        # VerifyAdminPin (Protección de endpoints DELETE)
│   │   ├── Requests/          # Form Requests dedicados (Validación + Anti-duplicados)
│   │   └── Resources/         # API Resources (DTOs tipados)
│   ├── Models/                # Product, Order, OrderItem, Expense, ReportTemplate, User
│   └── Providers/             # AppServiceProvider (Configuración JsonResource)
├── bootstrap/                 # bootstrap/app.php (Registro de alias de middleware)
├── database/
│   ├── migrations/            # Esquemas de base de datos e índices compuestos de alto rendimiento
│   └── seeders/               # Seeds y scripts SQL de simulación operativa mensual
├── routes/
│   └── api.php                # Definición de rutas REST y middleware de seguridad
└── tests/
    └── Feature/               # Tests de integración y seguridad (AdminPinSecurityTest)
```

---

## ⚡ Comandos Esenciales

```bash
# Iniciar servidor de desarrollo API (puerto 8000)
php artisan serve

# Ejecutar migraciones de base de datos
php artisan migrate

# Ejecutar suite de pruebas unitarias y de seguridad (Feature Tests)
php artisan test

# Consola interactiva para inspección directa de modelos
php artisan tinker

# Listar todas las rutas registradas en la API
php artisan route:list --path=api
```
