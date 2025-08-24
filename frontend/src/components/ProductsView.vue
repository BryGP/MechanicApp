
// frontend/src/components/ProductsView.vue
<script setup>
import { onMounted } from 'vue';
import { useStore } from '../store';

const store = useStore();
onMounted(() => {
  store.fetchProducts();
});
</script>

<template>
  <div>
    <h2>Productos</h2>
    <ul v-if="store.products.length">
      <li v-for="p in store.products" :key="p.id">
        <strong>{{ p.sku }}</strong> — {{ p.name }} — ${{ p.price }} (stock: {{ p.stock }})
      </li>
    </ul>
    <p v-else>Cargando productos...</p>
  </div>
</template>