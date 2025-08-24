<template>
  <div class="dashboard">
    <div class="cards">
      <div class="card product-card">
        <h2>Productos</h2>
        <p>Total: {{ products.length }}</p>
      </div>
      <div class="card order-card">
        <h2>Órdenes Abiertas</h2>
        <p>En progreso: {{ openOrders }}</p>
      </div>
    </div>
    <div class="orders-table">
      <h3>Listado de Órdenes</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Vehículo</th>
            <th>Estado</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>#{{ order.id }}</td>
            <td>{{ order.customer_name }}</td>
            <td>{{ order.vehicle }}</td>
            <td>{{ order.status }}</td>
            <td>${{ order.total }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useStore } from '../store/index.js';

const store = useStore();

onMounted(async () => {
  await store.fetchProducts();
  await store.fetchOrders();
});

const products = computed(() => store.products);
const orders = computed(() => store.orders);
const openOrders = computed(() => store.orders.filter(o => o.status === 'En proceso').length);
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.cards {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.card {
  flex: 1;
  padding: 1rem;
  border-radius: 8px;
  color: #fff;
}
.product-card {
  background-color: #f57c00;
}
.order-card {
  background-color: #e53935;
}
.orders-table table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
}
.orders-table th,
.orders-table td {
  padding: 0.5rem;
  border: 1px solid #eee;
  text-align: left;
  font-size: 0.9rem;
}
.orders-table tr:nth-child(even) {
  background-color: #fafafa;
}
</style>