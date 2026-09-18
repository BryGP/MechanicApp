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
      <StatCard icon="box" label="Productos en inventario" :value="store.products.length" color="var(--blue)" />
      <StatCard icon="alert" label="Alertas de stock bajo" :value="store.lowStockProducts.length" color="var(--danger)" />
      <StatCard icon="orders" label="Ordenes activas" :value="store.openOrders.length" color="var(--accent)" />
      <StatCard icon="revenue" label="Total recaudado" :value="'$' + store.totalRevenue.toFixed(2)" color="var(--success)" />
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
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
          </div>
          <p>No hay ordenes activas. <router-link to="/ordenes">Crear una orden</router-link></p>
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
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <p>Todo el inventario está en niveles óptimos</p>
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
.low-stock-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 14px;
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  transition: transform 0.2s var(--ease-spring), border-color 0.2s ease, background 0.2s ease;
}
.low-stock-item:hover {
  border-color: rgba(148, 163, 184, 0.25);
  transform: translateX(3px);
  background: #192748;
}
@media (max-width: 900px) { .dash-grid { grid-template-columns: 1fr; } }
</style>