<template>
  <teleport to="body">
    <transition name="overlay">
      <div v-if="modelValue" class="overlay" @click.self="$emit('update:modelValue', false)">
        <div :class="['modal-box', wide ? 'wide' : '']">
          <div class="modal-header">
            <slot name="header">
              <span class="modal-title">{{ title }}</span>
            </slot>
            <button class="btn-icon close-btn" @click="$emit('update:modelValue', false)" title="Cerrar ventana">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="17" height="17">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <slot />
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
defineProps({ modelValue: Boolean, title: String, wide: Boolean })
defineEmits(['update:modelValue'])
</script>

<style scoped>
.close-btn {
  transition: transform 0.2s var(--ease-spring), color 0.2s ease, background 0.2s ease;
}
.close-btn:hover {
  transform: rotate(90deg) scale(1.08);
  color: var(--text);
  background: var(--bg-hover);
}
</style>