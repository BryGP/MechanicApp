import { createRouter, createWebHistory } from 'vue-router';
import ProductsView from '../components/ProductsView.vue';
import OrdersView from '../components/OrdersView.vue';

/**
 * Vue Router configuration — MechanicApp
 *
 * Uses HTML5 History Mode (createWebHistory) for clean URLs without hash (#).
 * The Vite dev server handles 404 fallback automatically in development.
 *
 * Routes:
 *   /         ? ProductsView  — inventory list
 *   /orders   ? OrdersView    — service orders list + creation
 */
const routes = [
  {
    path: '/',
    name: 'Products',
    component: ProductsView,
  },
  {
    path: '/orders',
    name: 'Orders',
    component: OrdersView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
