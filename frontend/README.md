# MechanicApp — Frontend SPA (Vue 3 + Vite)

Interfaz de usuario moderna tipo Single Page Application (SPA) para el ERP de taller mecánico, diseñada con una estética oscura premium, micro-animaciones fluidas y soporte responsivo de alta productividad.

---

## 🏛️ Estructura y Módulos de la Aplicación

```
frontend/
├── src/
│   ├── main.js                  # Inicialización de Vue 3, Pinia y Router
│   ├── App.vue                  # Layout maestro: Sidebar colapsable + Header + Content
│   ├── views/                   # Vistas principales de navegación
│   │   ├── DashboardView.vue    # KPIs ejecutivos, órdenes activas y stock crítico
│   │   ├── ProductsView.vue     # Catálogo de refacciones físicas y mano de obra
│   │   ├── OrdersView.vue       # Gestión de órdenes a pantalla completa con paginador
│   │   ├── AccountingView.vue   # Flujo contable, balance y suite de gráficas animadas
│   │   └── ReportsView.vue      # Motor analítico de reportes SQL y exportación a portapapeles
│   ├── components/
│   │   ├── layout/              # Sidebar.vue y AppHeader.vue (Selector de rol Operador/Admin)
│   │   ├── ui/                  # Componentes reutilizables (StatCard, Badges, Modales, Toast)
│   │   ├── charts/              # AnimatedBarChart.vue (Barras proporcionales animadas)
│   │   └── analytics/           # FinancialAnalyticsSection.vue (Estadísticas y flujo de caja)
│   ├── composables/             # useAuth (Roles y PIN), useConfirm (Modal asíncrono), useToast
│   ├── store/                   # Pinia store (Productos, Órdenes, Egresos, Reportes)
│   ├── utils/                   # apiClient.js (Cliente HTTP) y format.js (Moneda MXN y fechas)
│   └── assets/styles/           # main.css (Sistema de diseño, tokens, scrollbars y animaciones)
├── index.html                   # Shell HTML
└── vite.config.js               # Configuración del servidor de desarrollo y alias
```

---

## 🚀 Capacidades Destacadas del Frontend

### 1. ⚡ Cliente HTTP Centralizado (`src/utils/apiClient.js`)
- Gestiona de forma unificada las peticiones al backend (`apiClient.get`, `apiClient.post`, etc.).
- Inyecta de forma transparente la cabecera de seguridad `X-Admin-Pin` en operaciones protegidas.
- Procesa y extrae mensajes amigables ante errores de validación de Laravel (códigos 422 con arrays de campos).

### 2. 📄 Paginación Inteligente y Layout a Pantalla Completa
- **Selector de Cantidad:** Opciones para mostrar `10`, `20`, `50` o `100` registros por vista.
- **Sin Espacio Muerto:** Las tablas de órdenes y egresos aprovechan el 100% de la altura útil de la pantalla con encabezados fijos (*sticky*) y barra de paginación anclada al fondo.
- **Contador Dinámico:** Muestra con precisión el rango de registros visibles (ej. *"Mostrando 1 a 10 de 75 órdenes"*).

### 3. 📊 Suite de Gráficas Animadas (`AnimatedBarChart.vue`)
- Barras de progreso proporcionales con transiciones bézier elásticas que crecen de 0% a su valor real al entrar a la sección.
- Desglose visual de gastos por categoría, absorción de costos y rendimiento vehicular en tiempo real.

### 4. 🛡️ Control de Acceso y Modales con PIN (`useAuth` & `useConfirm`)
- Alternador de rol en el encabezado (*Operador* vs *Administrador*).
- Modal centralizado de confirmación con teclado de PIN, auto-limpieza ante error y animación de vibración (*shake*) para prevenir borrados accidentales.

---

## ⚡ Comandos de Desarrollo

```bash
# Iniciar servidor de desarrollo con Hot Module Replacement (puerto 5173)
npm run dev

# Compilar bundle optimizado para producción en dist/
npm run build

# Previsualizar localmente la compilación de producción
npm run serve
```
