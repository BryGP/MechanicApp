/**
 * @fileoverview Vue Router Configuration
 * @module router/index
 * @description Configures client-side routing, view mapping, and HTML5 history mode
 * for the automotive workshop application.
 */

import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import ProductsView from '../views/ProductsView.vue'
import OrdersView from '../views/OrdersView.vue'
import AccountingView from '../views/AccountingView.vue'
import ReportsView from '../views/ReportsView.vue'

/**
 * Application route definitions mapping URL paths to single-page view components.
 * @type {Array<import('vue-router').RouteRecordRaw>}
 */
const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: DashboardView,
    meta: { title: 'Dashboard - Resumen Operativo' },
  },
  {
    path: '/productos',
    name: 'Products',
    component: ProductsView,
    meta: { title: 'Inventario de Refacciones y Servicios' },
  },
  {
    path: '/ordenes',
    name: 'Orders',
    component: OrdersView,
    meta: { title: 'Órdenes de Trabajo y Servicio' },
  },
  {
    path: '/contabilidad',
    name: 'Accounting',
    component: AccountingView,
    meta: { title: 'Administración & Finanzas' },
  },
  {
    path: '/reportes',
    name: 'Reports',
    component: ReportsView,
    meta: { title: 'Reportes y Analíticas de Taller' },
  },
]

/**
 * Configured Vue Router instance using standard HTML5 browser history mode.
 */
export default createRouter({
  history: createWebHistory(),
  routes,
})