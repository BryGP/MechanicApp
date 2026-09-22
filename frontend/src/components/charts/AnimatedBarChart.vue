<template>
  <div class="animated-chart-card">
    <div class="chart-header" v-if="title || subtitle">
      <div>
        <h4 class="chart-title">{{ title }}</h4>
        <p class="chart-subtitle" v-if="subtitle">{{ subtitle }}</p>
      </div>
      <slot name="header-action" />
    </div>

    <div class="chart-items-list" v-if="chartItems.length">
      <div
        v-for="(item, idx) in chartItems"
        :key="item.id || idx"
        class="chart-item-row"
        :style="{ animationDelay: `${idx * 0.08}s` }"
      >
        <!-- Item Info Header -->
        <div class="chart-item-info">
          <div class="chart-item-left">
            <span
              v-if="item.badge"
              class="cat-pill"
              :class="item.badgeClass || ''"
              style="margin-right: 6px; font-size: 0.72rem; padding: 2px 8px;"
            >
              {{ item.badge }}
            </span>
            <span class="chart-item-label">{{ item.label }}</span>
            <span class="chart-item-count" v-if="item.count !== undefined">({{ item.count }} ops)</span>
          </div>
          <div class="chart-item-values">
            <span class="chart-item-amount font-semibold">
              {{ formatCurrency(item.amount) }}
            </span>
            <span class="chart-item-pct font-mono">
              {{ item.pct.toFixed(1) }}%
            </span>
          </div>
        </div>

        <!-- Progress Bar Track -->
        <div class="chart-bar-track">
          <div
            class="chart-bar-fill"
            :style="{
              width: mounted ? `${item.pct}%` : '0%',
              backgroundColor: item.color || 'var(--accent)',
              boxShadow: `0 0 10px ${item.color || 'var(--accent)'}40`,
              transitionDelay: `${idx * 0.07}s`
            }"
          >
            <div class="chart-bar-glow"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="empty-chart" v-else>
      <span>No hay datos suficientes para graficar.</span>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Reusable Animated Progress Bar Chart Component
 * @module components/charts/AnimatedBarChart
 * @description Renders sleek, animated horizontal bar gauges for proportional data breakdown
 * (e.g. expenses by category, revenue distribution, parts demand).
 */

import { ref, computed, onMounted } from 'vue'
import { formatCurrency } from '../../utils/format'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  items: {
    type: Array,
    default: () => []
  },
  /** Optional custom total to calculate percentages. If not provided, sums items' amounts. */
  totalOverride: {
    type: Number,
    default: null
  }
})

const mounted = ref(false)

onMounted(() => {
  // Trigger fluid CSS width transition right after component mounts
  requestAnimationFrame(() => {
    setTimeout(() => {
      mounted.value = true
    }, 60)
  })
})

const computedTotal = computed(() => {
  if (props.totalOverride !== null && props.totalOverride > 0) {
    return props.totalOverride
  }
  return props.items.reduce((sum, it) => sum + (parseFloat(it.amount) || 0), 0)
})

const chartItems = computed(() => {
  const tot = computedTotal.value || 1
  return props.items.map((it) => {
    const amt = parseFloat(it.amount) || 0
    const pct = tot > 0 ? Math.min(100, Math.max(0, (amt / tot) * 100)) : 0
    return {
      ...it,
      amount: amt,
      pct
    }
  })
})
</script>

<style scoped>
.animated-chart-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem 1.5rem;
  box-shadow: var(--shadow-card);
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.animated-chart-card:hover {
  border-color: rgba(148, 163, 184, 0.25);
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  gap: 12px;
}

.chart-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.01em;
}

.chart-subtitle {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.chart-items-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.chart-item-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  animation: fadeInUp 0.4s ease both;
}

.chart-item-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.84rem;
}

.chart-item-left {
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;
}

.chart-item-label {
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chart-item-count {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.chart-item-values {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.chart-item-amount {
  color: var(--text);
  font-size: 0.875rem;
}

.chart-item-pct {
  font-size: 0.78rem;
  color: var(--text-muted);
  width: 44px;
  text-align: right;
}

.chart-bar-track {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.055);
  border-radius: 9999px;
  overflow: hidden;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.chart-bar-fill {
  height: 100%;
  border-radius: 9999px;
  position: relative;
  transition: width 1.1s cubic-bezier(0.16, 1, 0.3, 1);
}

.chart-bar-glow {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 20px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45));
  border-radius: 9999px;
}

.empty-chart {
  padding: 2rem 0;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.85rem;
}
</style>
