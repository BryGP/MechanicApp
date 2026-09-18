<!--
  ProductsView.vue
  =================
  Route: /  (default/home page)

  Displays the full list of workshop inventory products fetched from
  GET /api/products. Each item shows SKU, name, price, and current stock.

  Data flow:
    1. onMounted ? store.fetchProducts() ? GET /api/products
    2. store.products (reactive) ? renders the list automatically

  Future improvements:
    - Add "create product" form
    - Highlight items where stock < min_stock (low inventory warning)
    - Add edit/delete actions per row
-->
<script setup>
import { onMounted } from 'vue';
import { useStore } from '../store';

const store = useStore();

// Fetch products from the API when this view is first rendered
onMounted(() => {
  store.fetchProducts();
});
</script>

<template>
  <div>
    <h2>Productos</h2>

    <!-- Product list: renders once the store has data -->
    <ul v-if="store.products.length">
      <li v-for="p in store.products" :key="p.id">
        <strong>{{ p.sku }}</strong> — {{ p.name }} — ${{ p.price }} (stock: {{ p.stock }})
      </li>
    </ul>

    <!-- Placeholder while the fetch request is in flight -->
    <p v-else>Cargando productos...</p>
  </div>
</template>
