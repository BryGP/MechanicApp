<template>
  <div class="orders">
    <h2>Órdenes</h2>
    <button class="new-order" @click="createOrder">Nueva Orden</button>
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
</template>

<script setup>
import { useStore } from '../store/index.js';
import { onMounted } from 'vue';

const store = useStore();

onMounted(async () => {
  await store.fetchOrders();
});

const orders = store.$state.orders;

const createOrder = () => {
  // Aquí iría la lógica para mostrar un formulario y almacenar una nueva orden
  alert('Funcionalidad de crear orden pendiente.');
};
</script>

<style scoped>
.orders {
  background-color: #fff;
  padding: 1rem;
  border-radius: 8px;
}
.new-order {
  background-color: #fdd835;
  color: #000;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  margin-bottom: 1rem;
  cursor: pointer;
}
.new-order:hover {
  background-color: #fbc02d;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
  text-align: left;
}
tr:nth-child(even) {
  background-color: #fafafa;
}
</style>