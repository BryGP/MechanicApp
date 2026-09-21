<template>
  <th
    class="th-sortable"
    :class="{ 'th-active': currentField === field }"
    @click="$emit('sort', field)"
    :title="`Clic para ordenar por ${label}`"
  >
    <div class="th-content">
      <span>{{ label }}</span>
      <span class="sort-icon" :class="{ active: currentField === field }">
        <!-- Ascending sort indicator -->
        <svg
          v-if="currentField === field && currentOrder === 'asc'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="sort-svg"
        >
          <polyline points="18 15 12 9 6 15" />
        </svg>

        <!-- Descending sort indicator -->
        <svg
          v-else-if="currentField === field && currentOrder === 'desc'"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="sort-svg"
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>

        <!-- Neutral indicator (click to sort) -->
        <svg
          v-else
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.75"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="sort-svg"
          style="opacity: 0.6"
        >
          <polyline points="8 9 12 5 16 9" />
          <polyline points="16 15 12 19 8 15" />
        </svg>
      </span>
    </div>
  </th>
</template>

<script setup>
/**
 * @fileoverview Sortable Table Header Component
 * @module components/ui/SortableTh
 * @description Renders a clickable table header cell with active sorting indicators
 * (ascending, descending, or neutral toggle states).
 */

/**
 * Component Props
 * @property {string} label - Human-readable column title displayed to the user
 * @property {string} field - Unique property key used for sorting the dataset
 * @property {string} currentField - Active column currently being sorted in the parent view
 * @property {'asc'|'desc'} [currentOrder='asc'] - Active sort direction
 */
defineProps({
  label: { type: String, required: true },
  field: { type: String, required: true },
  currentField: { type: String, required: true },
  currentOrder: { type: String, default: 'asc' },
})

/**
 * Component Emits
 * @fires sort - Dispatched when the column header is clicked with the target field name
 */
defineEmits(['sort'])
</script>
