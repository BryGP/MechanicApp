<template>
  <header class="header">
    <div class="header-info">
      <h1 class="route-title">{{ title }}</h1>
      <span class="route-sub">{{ subtitle }}</span>
    </div>
    <div class="header-right">
      <div class="status-dot">
        <span class="dot"></span>
        <span class="status-text">Sistema activo</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const titles = {
  Dashboard:  { label: 'Dashboard', sub: 'Resumen general del taller automotriz' },
  Products:   { label: 'Inventario de Refacciones', sub: 'Gestión de productos y control de stock' },
  Orders:     { label: 'Órdenes de Servicio', sub: 'Mantenimientos y reparaciones activas' },
  Accounting: { label: 'Contabilidad & Finanzas', sub: 'Flujo de caja, egresos operativos y utilidad' },
  Reports:    { label: 'Centro de Reportes & SQL', sub: 'Plantillas automáticas de base de datos MySQL' },
}
const title    = computed(() => titles[route.name]?.label ?? route.name)
const subtitle = computed(() => titles[route.name]?.sub ?? '')
</script>

<style scoped>
.header {
  height: var(--header-h); min-height: var(--header-h);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 0 2rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-base);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  position: relative;
  z-index: 10;
}
.header-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.route-title {
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text);
  transition: color var(--transition);
}
.route-sub {
  font-size: 0.78rem;
  color: var(--text-muted);
}
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.status-dot {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-muted);
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  padding: 5px 12px;
  border-radius: 20px;
}
.dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--success);
  box-shadow: 0 0 8px var(--success);
  animation: pulse 2.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}
</style>