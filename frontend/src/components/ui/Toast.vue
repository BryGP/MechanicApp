<template>
  <div class="toast-stack">
    <transition-group name="toast-pop">
      <div v-for="t in toasts" :key="t.id" :class="['toast-item', `toast-${t.type}`]">
        <div class="toast-icon-wrap">
          <svg v-if="t.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="toast-svg">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <svg v-else-if="t.type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="toast-svg">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="toast-svg">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
        </div>
        <span class="toast-msg">{{ t.message }}</span>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast'
const { toasts } = useToast()
</script>

<style scoped>
.toast-stack {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}
.toast-item {
  pointer-events: auto;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  min-width: 280px;
  max-width: 420px;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(148, 163, 184, 0.12);
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-icon-wrap {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.toast-svg {
  width: 15px;
  height: 15px;
}

.toast-success {
  background: rgba(6, 36, 25, 0.92);
  border: 1px solid rgba(16, 185, 129, 0.35);
  color: #34d399;
}
.toast-success .toast-icon-wrap {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.toast-error {
  background: rgba(47, 9, 17, 0.92);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #f87171;
}
.toast-error .toast-icon-wrap {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.toast-info {
  background: rgba(11, 30, 56, 0.92);
  border: 1px solid rgba(37, 99, 235, 0.35);
  color: #60a5fa;
}
.toast-info .toast-icon-wrap {
  background: rgba(37, 99, 235, 0.2);
  color: #38bdf8;
}

.toast-msg {
  flex: 1;
  line-height: 1.35;
}

/* Animación pop suave */
.toast-pop-enter-active {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-pop-leave-active {
  transition: all 0.2s ease-in;
}
.toast-pop-enter-from {
  opacity: 0;
  transform: translateY(-10px) scale(0.96);
}
.toast-pop-leave-to {
  opacity: 0;
  transform: translateX(30px) scale(0.96);
}
</style>