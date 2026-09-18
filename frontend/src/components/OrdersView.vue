<!--
  OrdersView.vue
  ===============
  Route: /orders

  Displays all service orders and provides a demo "Create Order" action.
  Each order row includes its line items (products used, qty, price, subtotal).

  Data flow:
    1. onMounted ? store.fetchOrders() ? GET /api/orders
    2. store.orders (reactive) ? renders the table
    3. "Crear orden demo" button ? store.createOrder() ? POST /api/orders
       The backend decrements stock and calculates totals automatically.

  Note: asyncCreateDemo() uses hardcoded product_id: 1.
  Make sure at least one product exists (run the seeder) before testing.
-->
<script setup>
import { onMounted, ref } from 'vue';
import { useStore } from '../store';

const store = useStore();

// Fetch orders when this view mounts
onMounted(() => {
  store.fetchOrders();
});

/** Controls the loading state of the demo create button */
const creating = ref(false);

/**
 * Demo function: creates a hardcoded order for quick API testing.
 * In a real flow, this would come from a user-filled form.
 */
const asyncCreateDemo = async () => {
  try {
    creating.value = true;
    await store.createOrder({
      customer_name: 'Cliente demo',
      vehicle: 'Jetta 2013',
      items: [
        { product_id: 1, qty: 1 } // requires product with id=1 to exist
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

    <!-- Demo button: creates a sample order using product_id=1 -->
    <button @click="asyncCreateDemo" :disabled="creating">
      {{ creating ? 'Creando...' : 'Crear orden demo (1 item)' }}
    </button>

    <!-- Orders table: visible once at least one order exists -->
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
          <!-- Expand each order's line items inline -->
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

    <!-- Empty state -->
    <p v-else>Sin órdenes por ahora.</p>
  </div>
</template>
