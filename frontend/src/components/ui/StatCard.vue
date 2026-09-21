<template>
  <div class="stat-card">
    <div class="stat-icon" :style="{ background: `${color}18`, color }">
      <!-- SVG icon picker based on icon name -->
      <svg v-if="icon === 'box'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
        <line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
      <svg v-else-if="icon === 'alert'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        <line x1="12" y1="9" x2="12" y2="13"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
      </svg>
      <svg v-else-if="icon === 'orders'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        <path d="M9 14l2 2 4-4"/>
      </svg>
      <svg v-else-if="icon === 'revenue'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <line x1="12" y1="1" x2="12" y2="23"/>
        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg>
      <svg v-else-if="icon === 'expenses'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
        <polyline points="17 18 23 18 23 12"/>
      </svg>
      <svg v-else-if="icon === 'profit'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
        <polyline points="17 6 23 6 23 12"/>
      </svg>
      <svg v-else-if="icon === 'margin'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <svg v-else-if="icon === 'tag'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
        <line x1="7" y1="7" x2="7.01" y2="7"/>
      </svg>
      <svg v-else-if="icon === 'tools'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-ic">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
      </svg>
      <slot v-else></slot>
    </div>
    <div class="stat-info">
      <div class="stat-value">{{ value }}</div>
      <div class="stat-label">{{ label }}</div>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Key Performance Indicator (KPI) Stat Card
 * @module components/ui/StatCard
 * @description Displays high-level workshop metrics (Revenue, Expenses, Margins, Orders, Inventory)
 * with dedicated SVG icons, customizable theme accent tints, and hover animations.
 */

/**
 * Component Props
 * @property {'box'|'alert'|'orders'|'revenue'|'expenses'|'profit'|'margin'|'tag'|'tools'} [icon] - Pre-rendered SVG icon identifier
 * @property {string} label - Metric label or description (e.g. "Ingresos por Servicios")
 * @property {string|number} value - Formatted value or count to display
 * @property {string} [color='var(--accent)'] - Hex code or CSS variable for icon background tint and accents
 */
defineProps({
  icon: String,
  label: String,
  value: [String, Number],
  color: { type: String, default: 'var(--accent)' }
})
</script>

<style scoped>
.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.15rem;
  box-shadow: var(--shadow-card);
  transition: transform 0.24s var(--ease-spring), box-shadow 0.24s var(--ease-spring), border-color 0.24s var(--ease-spring);
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-card-hover);
  border-color: rgba(148, 163, 184, 0.24);
}
.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.24s var(--ease-spring), box-shadow 0.24s var(--ease-spring);
}
.stat-card:hover .stat-icon {
  transform: scale(1.06);
}
.svg-ic {
  width: 24px;
  height: 24px;
}
.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -0.02em;
}
.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 5px;
  font-weight: 500;
}
</style>