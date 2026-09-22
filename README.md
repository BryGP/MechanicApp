# AutoServicio — ERP & Sistema de Gestión para Taller Mecánico

Sistema integral de administración y control operativo para talleres automotrices modernos, especializado en vehículos de combustión, eléctricos (EV) y clásicos de colección. Desarrollado con una arquitectura desacoplada: **Laravel 11 REST API** en el backend y **Vue 3 SPA** con diseño oscuro de alta gama en el frontend.

---

## 🚀 Módulos y Funcionalidades Principales

### 1. 📊 Centro de Control (Dashboard)
- **KPIs en Tiempo Real:** Métricas clave de productos en almacén, alertas de stock crítico, órdenes activas e ingresos totales recaudados.
- **Acceso Rápido:** Visualización de las órdenes más recientes y panel de refacciones con inventario bajo.

### 2. 🚗 Órdenes de Servicio & Taller
- **Flujo Operativo Completo:** Ciclo de vida estructurado por estados: `Abierta` ➔ `En proceso` ➔ `Terminada` ➔ `Entregada`.
- **Selector Inteligente de Partidas:** Registro multi-item que combina catálogo de refacciones físicas y mano de obra/servicios técnicos con subtotalizado dinámico y deducción atómica de inventario.
- **Modal de Detalle & Ticket de Impresión:** Inspección profunda del vehículo, notas de diagnóstico técnico, desglose de partidas y generador de ticket imprimible en PDF (`window.print()`).
- **Navegación Eficiente:** Contenedor a pantalla completa con scroll interno, encabezados fijos (*sticky*) y selector de paginación (`10 | 20 | 50 | 100` órdenes por vista).

### 3. 📦 Catálogo de Productos & Refacciones
- **Doble Naturaleza de Catálogo:** Distinción estricta entre refacciones físicas (con control de existencias) y servicios intangibles de mano de obra (`is_service`).
- **Filtros de Salud de Inventario:** Clasificación por estado de stock (*Óptimo*, *Stock Bajo*, *Sin Stock*) excluyendo servicios para evitar falsas alertas.
- **Validación Anti-Duplicados:** Control de unicidad por código SKU y nombre de producto.

### 4. 💼 Administración & Finanzas (Contabilidad)
- **Libro Contable Operativo:** Registro de egresos clasificados (Nómina, Refacciones, Herramientas, Renta, Servicios, Otros) con métodos de pago y folios fiscales.
- **Flujo de Caja en Vivo:** Balance consolidado de Ingresos por Servicios, Gastos Operativos, Utilidad Neta Real y Margen Operativo porcentual.
- **Suite de Analítica & Gráficas Animadas:**
  - *Desglose de Gastos por Categoría:* Barras horizontales proporcionales animadas con transiciones CSS fluidas.
  - *Balance y Absorción:* Comparativa visual de ingresos vs gastos con indicador de retorno por peso invertido.
  - *Métricas Vehiculares:* Rendimiento por vehículo ingresado, ticket promedio facturado y modelos con mayor actividad en taller.
- **Paginador y Scroll Ampliado:** Selector de registros (`10 | 20 | 50 | 100`) y fechas legibles formateadas en español.

### 5. 📈 Centro de Reportes & Business Intelligence
- **Catálogo Analítico:** Motor de consultas SQL estructuradas en categorías gerenciales: *Inventario*, *Ventas*, *Operaciones* y *Finanzas*.
- **Exportación Rápida:** Copiado al portapapeles en formato tabular para hojas de cálculo (Excel / Google Sheets).
- **Resumen Ejecutivo:** Diagnóstico de salud financiera, valoración de inventario muerto y stock crítico.

### 6. 🛡️ Seguridad y Control de Acceso (Operador vs Administrador)
- **Segregación de Roles:** Modo *Operador* para captura cotidiana y modo *Administrador* desbloqueable mediante PIN de seguridad.
- **Protección Full-Stack:** Las acciones destructivas (eliminación de órdenes de trabajo o partidas contables de egresos) requieren confirmación de PIN en la interfaz y son validadas a nivel HTTP mediante el middleware `VerifyAdminPin` (`X-Admin-Pin`).

---

## 🏛️ Arquitectura del Sistema

```
mechanic-app/
├── backend/                  # Laravel 11 REST API (PHP 8.2+)
│   ├── app/Http/Controllers  # Controladores REST esbeltos
│   ├── app/Http/Requests     # Form Requests con validaciones dedicadas
│   ├── app/Http/Resources    # DTOs / API Resources con tipado estricto
│   ├── app/Http/Middleware   # Middleware de autorización con PIN de Admin
│   ├── app/Models            # Modelos Eloquent y relaciones ORM
│   └── database/             # Migraciones con índices compuestos y seeds
│
└── frontend/                 # Single Page Application (Vue 3 + Vite)
    ├── src/views/            # Vistas (Dashboard, Productos, Órdenes, Contabilidad, Reportes)
    ├── src/components/       # Componentes UI modulares, gráficas y analítica
    ├── src/composables/      # Lógica compartida (Auth, Confirm, Toast)
    ├── src/store/            # Estado global con Pinia
    ├── src/utils/            # Cliente HTTP centralizado (apiClient) y formateadores
    └── src/assets/styles/    # Sistema de diseño CSS moderno con tokens oscuros
```

---

## 🗄️ Modelo de Datos Relacional

- **`products`:** `id`, `name`, `sku`, `price`, `stock`, `min_stock`, `is_service`, `description`.
- **`orders`:** `id`, `customer_name`, `vehicle`, `notes`, `status`, `total`, `created_at`, `updated_at`.
- **`order_items`:** `id`, `order_id` (FK), `product_id` (FK), `quantity`, `unit_price`, `subtotal`.
- **`expenses`:** `id`, `concept`, `category`, `amount`, `payment_method`, `reference`, `expense_date`.
- **`report_templates`:** `id`, `name`, `description`, `category`, `sql_query`.

*Optimizaciones de Rendimiento:* Índices compuestos en base de datos para acelerar consultas frecuentes:
- `orders (status, created_at)`
- `products (is_service, stock, min_stock)`
- `expenses (expense_date, category)`

---

## 🔌 Principales Endpoints de la API

| Módulo | Método | Endpoint | Descripción | Seguridad |
| :--- | :--- | :--- | :--- | :--- |
| **Salud** | `GET` | `/api/health` | Verificación de estado del servidor | Libre |
| **Productos** | `GET` | `/api/products` | Listado general ordenado | Libre |
| | `POST` | `/api/products` | Creación con validación `StoreProductRequest` | Libre |
| | `PUT` | `/api/products/{id}` | Edición con validación `UpdateProductRequest` | Libre |
| | `DELETE` | `/api/products/{id}` | Eliminación de producto | Libre |
| **Órdenes** | `GET` | `/api/orders` | Listado relacional de órdenes con items | Libre |
| | `POST` | `/api/orders` | Creación transaccional + deducción de stock | Libre |
| | `GET` | `/api/orders/{id}` | Consulta detallada de orden con items | Libre |
| | `PUT` | `/api/orders/{id}` | Actualización de estatus, cliente y vehículo | Libre |
| | `DELETE` | `/api/orders/{id}` | **Eliminación permanente de orden** | 🔒 **Requiere PIN Admin (`X-Admin-Pin`)** |
| **Egresos** | `GET` | `/api/expenses` | Historial de gastos contables | Libre |
| | `POST` | `/api/expenses` | Registro con prevención de duplicados | Libre |
| | `DELETE` | `/api/expenses/{id}` | **Baja permanente de registro de gasto** | 🔒 **Requiere PIN Admin (`X-Admin-Pin`)** |
| **Reportes** | `GET` | `/api/reports` | Catálogo de plantillas analíticas | Libre |
| | `POST` | `/api/reports/{id}/run` | Ejecución de consulta gerencial | Libre |

---

## 💻 Stack Tecnológico

| Capa | Tecnología | Versión | Propósito |
| :--- | :--- | :--- | :--- |
| **Backend** | Laravel | 11.x | Framework REST API robusto |
| **Lenguaje** | PHP | 8.2+ | Lógica de servidor y transacciones |
| **Base de Datos** | MySQL / MariaDB | 10.4+ | Persistencia relacional con índices compuestos |
| **Frontend** | Vue 3 | 3.4+ | Composition API con Single File Components (`.vue`) |
| **Build Tool** | Vite | 7.x | Bundler ultra-rápido con Hot Module Replacement (HMR) |
| **State Mgmt** | Pinia | 2.1+ | Almacén de estado reactivo global |
| **Routing** | Vue Router | 4.2+ | Navegación SPA fluida en modo HTML5 History |
| **Estilos** | Vanilla CSS | Modern | Tokens `:root`, estética oscura premium, animaciones fluidas |
