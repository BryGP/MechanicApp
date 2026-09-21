<template>
  <header class="header">
    <div class="header-breadcrumb">
      <span class="bc-root">Taller</span>
      <svg class="bc-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
      <span class="bc-page">{{ currentSection }}</span>
    </div>
    <div class="header-right">
      <div class="header-date">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="date-ic">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span>{{ formattedDate }}</span>
      </div>
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
const sectionNames = {
  Dashboard:  'Dashboard',
  Products:   'Inventario de Refacciones',
  Orders:     'Órdenes de Servicio',
  Accounting: 'Administración & Finanzas',
  Reports:    'Centro de Reportes',
}
const currentSection = computed(() => sectionNames[route.name] ?? route.name)

const formattedDate = computed(() => {
  const now = new Date()
  const formatted = now.toLocaleDateString('es-MX', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
  return formatted.charAt(0).toUpperCase() + formatted.slice(1)
})
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
.header-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
}
.bc-root {
  color: var(--text-muted);
  font-weight: 500;
}
.bc-arrow {
  width: 14px;
  height: 14px;
  color: var(--text-muted);
  opacity: 0.6;
}
.bc-page {
  color: var(--text);
  font-weight: 600;
  letter-spacing: -0.01em;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.header-date {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
}
.date-ic {
  width: 14px;
  height: 14px;
  opacity: 0.7;
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