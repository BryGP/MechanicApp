import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import ProductsView from '../views/ProductsView.vue'
import OrdersView from '../views/OrdersView.vue'
import AccountingView from '../views/AccountingView.vue'
import ReportsView from '../views/ReportsView.vue'

const routes = [
  { path: '/',             name: 'Dashboard',  component: DashboardView },
  { path: '/productos',    name: 'Products',   component: ProductsView },
  { path: '/ordenes',      name: 'Orders',     component: OrdersView },
  { path: '/contabilidad', name: 'Accounting', component: AccountingView },
  { path: '/reportes',     name: 'Reports',    component: ReportsView },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})