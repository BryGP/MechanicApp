<template>
  <teleport to="body">
    <transition name="overlay">
      <div v-if="modelValue" class="overlay" @click.self="$emit('update:modelValue', false)">
        <transition name="modal">
          <div v-if="modelValue" :class="['modal-box', wide ? 'wide' : '']">
            <div class="modal-header">
              <span class="modal-title">{{ title }}</span>
              <button class="btn-icon" @click="$emit('update:modelValue', false)" style="font-size:1.1rem">x</button>
            </div>
            <slot />
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>
<script setup>
defineProps({ modelValue: Boolean, title: String, wide: Boolean })
defineEmits(['update:modelValue'])
</script>