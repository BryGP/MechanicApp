<template>
  <span :class="['badge', cls]">{{ label }}</span>
</template>
<script setup>
/**
 * @fileoverview Inventory Stock Health Badge Component
 * @module components/ui/StockBadge
 * @description Renders visual indicators for physical product inventory levels:
 * - Depleted (stock === 0): Critical red badge
 * - Low stock (stock <= minStock): Warning amber badge
 * - Healthy stock (stock > minStock): Success green badge
 */

import { computed } from 'vue'

/**
 * Component Props
 * @property {number} stock - Current on-hand quantity
 * @property {number} minStock - Minimum required inventory safety threshold
 */
const props = defineProps({
  stock: { type: Number, required: true },
  minStock: { type: Number, required: true }
})

/** Computes the CSS styling modifier based on inventory thresholds */
const cls = computed(() => (
  props.stock === 0
    ? 'badge-critical'
    : props.stock <= props.minStock
      ? 'badge-low'
      : 'badge-ok'
))

/** Formats the user-facing status label */
const label = computed(() => (
  props.stock === 0
    ? 'Sin stock'
    : props.stock <= props.minStock
      ? `Bajo (${props.stock})`
      : `OK (${props.stock})`
))
</script>
<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 11px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}
.badge::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}
.badge-ok {
  background: #062419;
  color: #34d399;
  border: 1px solid #059669;
}
.badge-ok::before {
  background: #10b981;
  box-shadow: 0 0 6px #10b981;
}

.badge-low {
  background: #2e1a05;
  color: #fbbf24;
  border: 1px solid #d97706;
}
.badge-low::before {
  background: #f59e0b;
  box-shadow: 0 0 6px #f59e0b;
}

.badge-critical {
  background: #2f0911;
  color: #f87171;
  border: 1px solid #dc2626;
}
.badge-critical::before {
  background: #ef4444;
  box-shadow: 0 0 6px #ef4444;
}
</style>