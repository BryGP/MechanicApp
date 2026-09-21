<template>
  <div class="toast-stack">
    <transition-group name="toast-drop">
      <div 
        v-for="t in toasts" 
        :key="t.id" 
        :class="['toast-item', `toast-${t.type}`]"
        @click="remove(t.id)"
      >
        <div class="toast-icon-wrap">
          <svg v-if="t.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" class="toast-svg">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <svg v-else-if="t.type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" class="toast-svg">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" class="toast-svg">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
        </div>
        <div class="toast-content">
          <span class="toast-title">{{ t.type === 'error' ? 'Atención / Validación' : t.type === 'success' ? 'Operación Exitosa' : 'Aviso del Sistema' }}</span>
          <span class="toast-msg">{{ t.message }}</span>
        </div>
        <button class="toast-close" @click.stop="remove(t.id)" title="Cerrar notificación">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast'
const { toasts, remove } = useToast()
</script>

<style scoped>
.toast-stack {
  position: fixed;
  top: 1.75rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10000;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  pointer-events: none;
  width: auto;
}

.toast-item {
  pointer-events: auto;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 22px;
  border-radius: 14px;
  min-width: 360px;
  max-width: 600px;
  box-shadow: 0 20px 45px -8px rgba(0, 0, 0, 0.75), 0 4px 18px rgba(0, 0, 0, 0.35);
  transition: all 0.32s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
}

.toast-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.toast-svg {
  width: 19px;
  height: 19px;
}

.toast-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.toast-title {
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  opacity: 0.85;
}

.toast-msg {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.38;
}

.toast-close {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  opacity: 0.55;
  transition: opacity 0.15s, background 0.15s;
  color: inherit;
  flex-shrink: 0;
}
.toast-close:hover {
  opacity: 1;
  background: rgba(0, 0, 0, 0.09);
}

/* PALETA PASTEL LUMINOSA DE ALTO CONTRASTE */

/* ERROR: Pastel Rosa / Frambuesa suave iluminado con tipografía borgoña profunda */
.toast-error {
  background: linear-gradient(135deg, #ffe4e6 0%, #fff1f2 100%);
  border: 1.5px solid #fda4af;
  color: #881337;
}
.toast-error .toast-icon-wrap {
  background: #e11d48;
  color: #ffffff;
  box-shadow: 0 3px 10px rgba(225, 29, 72, 0.4);
}
.toast-error .toast-title {
  color: #be123c;
}

/* SUCCESS: Pastel Menta / Lima fresco con tipografía esmeralda profunda */
.toast-success {
  background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
  border: 1.5px solid #86efac;
  color: #14532d;
}
.toast-success .toast-icon-wrap {
  background: #10b981;
  color: #ffffff;
  box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
}
.toast-success .toast-title {
  color: #047857;
}

/* INFO: Pastel Celeste Ice Blue con tipografía zafiro profunda */
.toast-info {
  background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
  border: 1.5px solid #7dd3fc;
  color: #0c4a6e;
}
.toast-info .toast-icon-wrap {
  background: #0284c7;
  color: #ffffff;
  box-shadow: 0 3px 10px rgba(2, 132, 199, 0.4);
}
.toast-info .toast-title {
  color: #0369a1;
}

/* ANIMACIÓN: Descenso suave de arriba hacia abajo en el centro de la pantalla */
.toast-drop-enter-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-drop-leave-active {
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-drop-enter-from {
  opacity: 0;
  transform: translateY(-32px) scale(0.92);
}
.toast-drop-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}
</style>