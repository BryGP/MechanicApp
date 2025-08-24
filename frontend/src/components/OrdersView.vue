
// frontend/src/components/OrdersView.vue

<script setup>
import { onMounted, ref } from 'vue';
import { useStore } from '../store';
const store = useStore();

onMounted(() => {
  store.fetchOrders();
});

// (opcional) demo para crear orden rápida:
const creating = ref(false);
const asyncCreateDemo = async () => {
  try {
    creating.value = true;
    await store.createOrder({
      customer_name: 'Cliente demo',
      vehicle: 'Jetta 2013',
      items: [
        { product_id: 1, qty: 1 } // ajusta IDs reales de tu tabla
      ]
    });
  } finally {
    creating.value = false;
  }
};
</script>

<template>
  <div>
    <h2>Órdenes</h2>

    <button @click="asyncCreateDemo" :disabled="creating">
      {{ creating ? 'Creando...' : 'Crear orden demo (1 item)' }}
    </button>

    <table v-if="store.orders.length" style="margin-top:1rem; width:100%; background:#fff">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Vehículo</th>
          <th>Estatus</th>
          <th>Total</th>
          <th>Items</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="o in store.orders" :key="o.id">
          <td>{{ o.id }}</td>
          <td>{{ o.customer_name }}</td>
          <td>{{ o.vehicle }}</td>
          <td>{{ o.status }}</td>
          <td>${{ o.total }}</td>
          <td>
            <ul>
              <li v-for="it in (o.items || [])" :key="it.id">
                #{{ it.product_id }} x {{ it.qty }} — ${{ it.unit_price }} = ${{ it.subtotal }}
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-else>Sin órdenes por ahora.</p>
  </div>
</template>