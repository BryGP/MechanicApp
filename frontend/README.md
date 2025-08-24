# Frontend (Vue.js)

Este directorio contiene un esqueleto básico de una aplicación web construida con **Vue 3**, administrada con **Pinia**, y configurada mediante **Vite**. La idea es proveer una interfaz moderna y responsiva para la gestión del taller.

## Requisitos

- Node.js >= 18
- npm (o Yarn)

## Estructura

```
frontend/
├── public/
│   └── index.html
├── src/
│   ├── assets/
│   ├── components/
│   │   ├── Sidebar.vue
│   │   ├── Dashboard.vue
│   │   ├── OrdersList.vue
│   │   └── ProductsCard.vue
│   ├── router/
│   │   └── index.js
│   ├── store/
│   │   └── index.js
│   ├── App.vue
│   └── main.js
├── package.json
└── vite.config.js
```

## Instalación

1. Desde la carpeta `mechanic_app/frontend` ejecuta `npm install` para instalar las dependencias.
2. Levanta el entorno de desarrollo con `npm run dev`. La aplicación se servirá en `http://localhost:5173` de forma predeterminada.
3. Para generar una versión de producción ejecuta `npm run build`.

## Descripción de archivos

- `public/index.html`: Archivo HTML principal donde se monta la aplicación. Contiene metaetiquetas y punto de montaje (`<div id="app">`).
- `src/main.js`: Punto de entrada de Vue. Crea la aplicación, configura el router y el almacén (Pinia) y la monta en el DOM.
- `src/App.vue`: Componente raíz que incluye la estructura general (sidebar + contenido dinámico).
- `src/components`: Colección de componentes de interfaz reutilizables, como `Sidebar`, `Dashboard`, `OrdersList` y `ProductsCard`.
- `src/router/index.js`: Define las rutas de la SPA y los componentes asociados a cada vista.
- `src/store/index.js`: Configura el almacén central utilizando Pinia para gestionar el estado global (productos, órdenes, usuario, etc.).
- `vite.config.js`: Configuración de Vite para compilar el proyecto.