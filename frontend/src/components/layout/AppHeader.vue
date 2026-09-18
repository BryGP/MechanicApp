<template>
  <header class="header">
    <button class="menu-btn" @click="$emit('toggle')">
      <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
      </svg>
    </button>
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

defineEmits(['toggle'])

const route = useRoute()
const titles = {
  Dashboard: { label: 'Dashboard', sub: 'Resumen general del taller' },
  Products:  { label: 'Inventario', sub: 'Gestion de productos y refacciones' },
  Orders:    { label: 'Ordenes', sub: 'Ordenes de servicio activas' },
}
const title    = computed(() => titles[route.name]?.label ?? route.name)
const subtitle = computed(() => titles[route.name]?.sub ?? '')
</script>

<style scoped>
.header {
  height: var(--header-h); min-height: var(--header-h);
  display: flex; align-items: center; gap: 16px;
  padding: 0 2rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-base);
}
.menu-btn {
  width: 36px; height: 36px; border: 1px solid var(--border);
  border-radius: var(--radius-sm); background: transparent;
  color: var(--text-muted); cursor: pointer; display: flex;
  align-items: center; justify-content: center;
  transition: all var(--transition);
}
.menu-btn:hover { background: var(--bg-hover); color: var(--text); }
.header-info { flex: 1; }
.route-title { font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
.route-sub { font-size: 0.78rem; color: var(--text-muted); }
.header-right { display: flex; align-items: center; gap: 12px; }
.status-dot { display: flex; align-items: center; gap: 7px; font-size: 0.8rem; color: var(--text-muted); }
.dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--success);
  box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>