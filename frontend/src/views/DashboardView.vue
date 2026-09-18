<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Bienvenido de nuevo</h2>
        <p class="page-subtitle">Aqui tienes el resumen de tu taller</p>
      </div>
    </div>

    <!-- Stat cards -->
    <div class="stats-grid">
      <StatCard icon="&#128230;" label="Productos en inventario" :value="store.products.length" color="var(--blue)" />
      <StatCard icon="&#9888;" label="Alertas de stock bajo" :value="store.lowStockProducts.length" color="var(--danger)" />
      <StatCard icon="&#128203;" label="Ordenes activas" :value="store.openOrders.length" color="var(--accent)" />
      <StatCard icon="&#128176;" label="Total recaudado" :value="'$' + store.totalRevenue.toFixed(2)" color="var(--success)" />
    </div>

    <div class="dash-grid">
      <!-- Recent orders -->
      <div class="card">
        <div class="section-header">
          <span class="section-title">Ordenes recientes</span>
          <router-link to="/ordenes" class="btn btn-ghost btn-sm">Ver todas</router-link>
        </div>
        <table class="data-table" v-if="recentOrders.length">
          <thead>
            <tr>
              <th>#</th><th>Cliente</th><th>Vehiculo</th><th>Estatus</th><th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in recentOrders" :key="o.id">
              <td class="text-muted">#{{ o.id }}</td>
              <td>{{ o.customer_name || 'Sin nombre' }}</td>
              <td>{{ o.vehicle || '—' }}</td>
              <td><StatusBadge :status="o.status" /></td>
              <td class="font-semibold text-accent">${{ parseFloat(o.total).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="empty-state" v-else>
          <div class="icon">&#128203;</div>
          <p>No hay ordenes aun. <router-link to="/ordenes">Crea una</router-link></p>
        </div>
      </div>

      <!-- Low stock -->
      <div class="card">
        <div class="section-header">
          <span class="section-title">Stock bajo</span>
          <router-link to="/productos" class="btn btn-ghost btn-sm">Ver inventario</router-link>
        </div>
        <div v-if="store.lowStockProducts.length" class="low-stock-list">
          <div v-for="p in store.lowStockProducts" :key="p.id" class="low-stock-item">
            <div>
              <div class="font-semibold" style="font-size:0.875rem">{{ p.name }}</div>
              <div class="text-muted" style="font-size:0.75rem">SKU: {{ p.sku }}</div>
            </div>
            <StockBadge :stock="p.stock" :min-stock="p.min_stock" />
          </div>
        </div>
        <div class="empty-state" v-else>
          <div class="icon">&#9989;</div>
          <p>Todo el inventario esta en niveles normales</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useStore } from '../store'
import StatCard from '../components/ui/StatCard.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import StockBadge from '../components/ui/StockBadge.vue'

const store = useStore()
onMounted(async () => {
  await store.fetchProducts()
  await store.fetchOrders()
})
const recentOrders = computed(() => store.orders.slice(0, 5))
</script>

<style scoped>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.section-title { font-size: 0.9rem; font-weight: 700; }
.low-stock-list { display: flex; flex-direction: column; gap: 10px; }
.low-stock-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; background: var(--bg-hover); border-radius: var(--radius-sm); }
@media (max-width: 900px) { .dash-grid { grid-template-columns: 1fr; } }
</style>