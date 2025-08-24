import { createRouter, createWebHistory } from 'vue-router';
import ProductsView from '../components/ProductsView.vue';
import OrdersView from '../components/OrdersView.vue';

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