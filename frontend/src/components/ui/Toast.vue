<template>
  <div class="toast-stack">
    <transition-group name="toast-slide">
      <div v-for="t in toasts" :key="t.id" :class="['toast-item', `toast-${t.type}`]">
        <span class="toast-icon">{{ icons[t.type] }}</span>
        {{ t.message }}
      </div>
    </transition-group>
  </div>
</template>
<script setup>
import { useToast } from '../../composables/useToast'
const { toasts } = useToast()
const icons = { success: 'v', error: '!', info: 'i' }
</script>