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
        <!-- Ascendente -->
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

        <!-- Descendente -->
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

        <!-- Neutro (Click para ordenar) -->
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
defineProps({
  label: { type: String, required: true },
  field: { type: String, required: true },
  currentField: { type: String, required: true },
  currentOrder: { type: String, default: 'asc' },
})
defineEmits(['sort'])
</script>
