<template>
  <teleport to="body">
    <transition name="confirm-fade">
      <div v-if="isOpen" class="confirm-overlay" @click.self="cancel">
        <div class="confirm-card" role="dialog" aria-modal="true">
          <!-- Header Icon -->
          <div class="confirm-icon-wrap" :class="{ 'admin-lock': modalState.requiresAdmin }">
            <!-- Shield Lock for Admin Protected -->
            <svg v-if="modalState.requiresAdmin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="confirm-svg">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <rect x="9" y="11" width="6" height="5" rx="1"/>
              <path d="M10 11V9a2 2 0 0 1 4 0v2"/>
            </svg>
            <!-- Trash/Warning for standard delete -->
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="confirm-svg">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              <line x1="10" y1="11" x2="10" y2="17"/>
              <line x1="14" y1="11" x2="14" y2="17"/>
            </svg>
          </div>

          <!-- Title & Prompt -->
          <h3 class="confirm-title">{{ modalState.title }}</h3>
          
          <div class="confirm-body">
            <p class="confirm-message">
              {{ modalState.message }}
            </p>

            <div v-if="modalState.itemName" class="confirm-item-badge">
              {{ modalState.itemName }}
            </div>

            <!-- Notice for Admin Protected Items -->
            <div v-if="modalState.requiresAdmin" class="admin-notice-box">
              <div v-if="isAdmin" class="admin-verified-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><path d="M20 6L9 17l-5-5"/></svg>
                <span>Sesión de Administrador activa — Autorización concedida</span>
              </div>
              <div v-else class="admin-pin-prompt">
                <div class="pin-prompt-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Operación contable restringida
                </div>
                <p class="pin-prompt-desc">
                  El rol de operador no puede eliminar egresos ni órdenes sin autorización. Ingresa el <strong>PIN de Administrador</strong>:
                </p>
                <div class="pin-input-wrap">
                  <input
                    ref="pinInputRef"
                    v-model="inputPin"
                    type="password"
                    maxlength="8"
                    placeholder="PIN de Administrador"
                    class="form-input pin-input"
                    :class="{ 'pin-error': pinError }"
                    @keyup.enter="confirm"
                    @input="pinError = false"
                  />
                  <span v-if="pinError" class="pin-error-msg">PIN incorrecto. Operación denegada.</span>
                </div>
              </div>

              <div v-if="modalState.warningText" class="confirm-subwarning">
                {{ modalState.warningText }}
              </div>
            </div>
            <div v-else-if="modalState.warningText" class="confirm-subwarning-plain">
              {{ modalState.warningText }}
            </div>
          </div>

          <!-- Actions -->
          <div class="confirm-actions">
            <button class="btn btn-ghost confirm-btn-cancel" @click="cancel" type="button">
              {{ modalState.cancelText }}
            </button>
            <button
              class="btn btn-danger confirm-btn-delete"
              @click="confirm"
              type="button"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              <span>{{ modalState.confirmText }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
/**
 * @fileoverview Centralized Confirmation Modal Component
 * @module components/ui/ConfirmModal
 * @description Renders a modal confirmation dialog teleported to document body.
 * Intercepts high-privilege deletions and enforces Administrator PIN verification
 * if the operation is flagged as restricted (e.g. expenses, orders).
 */

import { ref, watch, nextTick } from 'vue'
import { useConfirm } from '../../composables/useConfirm'
import { useAuth } from '../../composables/useAuth'
import { useToast } from '../../composables/useToast'

/** State and handlers from the confirmation composable */
const { isOpen, modalState, handleConfirm, handleCancel } = useConfirm()

/** Authentication controls for PIN verification */
const { isAdmin, verifyPin } = useAuth()

/** Notification toast dispatcher */
const toast = useToast()

/** Reactive input value for PIN verification prompt */
const inputPin = ref('')

/** Flag indicating whether the entered PIN was invalid */
const pinError = ref(false)

/** DOM reference to PIN input element for auto-focus */
const pinInputRef = ref(null)

/**
 * Watches modal open state to reset input fields and trigger automatic focus
 * when the modal becomes visible to the operator.
 */
watch(isOpen, (newVal) => {
  if (newVal) {
    inputPin.value = ''
    pinError.value = false
    if (modalState.value.requiresAdmin && !isAdmin.value) {
      nextTick(() => {
        pinInputRef.value?.focus()
      })
    }
  }
})

/**
 * Validates PIN requirements (if active role is not admin) and executes confirmation callback.
 */
function confirm() {
  // If item requires admin and user is not admin, verify PIN
  if (modalState.value.requiresAdmin && !isAdmin.value) {
    if (!verifyPin(inputPin.value)) {
      pinError.value = true
      inputPin.value = ''
      toast.error('PIN de Administrador incorrecto. Operación no autorizada.')
      nextTick(() => {
        pinInputRef.value?.focus()
      })
      return
    }
  }

  handleConfirm()
}

/**
 * Cancels the confirmation dialog and rejects/resolves false to caller.
 */
function cancel() {
  handleCancel()
}
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  z-index: 11000;
  background: rgba(2, 6, 18, 0.82);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
}

.confirm-fade-enter-active, .confirm-fade-leave-active {
  transition: opacity 0.22s var(--ease-smooth);
}
.confirm-fade-enter-from, .confirm-fade-leave-to {
  opacity: 0;
}

.confirm-card {
  background: var(--bg-card);
  border: 1px solid rgba(148, 163, 184, 0.24);
  border-radius: 16px;
  width: 100%;
  max-width: 460px;
  padding: 1.75rem;
  box-shadow: 0 25px 65px -10px rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  animation: confirmPopIn 0.26s var(--ease-spring) forwards;
}

@keyframes confirmPopIn {
  0% {
    opacity: 0;
    transform: scale(0.92) translateY(12px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.confirm-icon-wrap {
  width: 58px;
  height: 58px;
  border-radius: 16px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.15rem;
  box-shadow: 0 4px 18px rgba(239, 68, 68, 0.25);
}
.confirm-icon-wrap.admin-lock {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(239, 68, 68, 0.15));
  border-color: rgba(245, 158, 11, 0.4);
  color: #f59e0b;
  box-shadow: 0 4px 18px rgba(245, 158, 11, 0.25);
}
.confirm-svg {
  width: 28px;
  height: 28px;
}

.confirm-title {
  font-size: 1.22rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  margin-bottom: 0.5rem;
}

.confirm-body {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.5rem;
}

.confirm-message {
  font-size: 0.94rem;
  color: var(--text-muted);
  line-height: 1.45;
}

.confirm-item-badge {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.95rem;
  font-weight: 700;
  color: #f8fafc;
  max-width: 100%;
  word-break: break-word;
}

.admin-notice-box {
  width: 100%;
  background: rgba(245, 158, 11, 0.06);
  border: 1px solid rgba(245, 158, 11, 0.22);
  border-radius: 10px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
}

.admin-verified-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.84rem;
  font-weight: 700;
  color: #34d399;
}

.pin-prompt-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #fbbf24;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.pin-prompt-desc {
  font-size: 0.82rem;
  color: var(--text-muted);
  line-height: 1.4;
}

.pin-input-wrap {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 4px;
}

.pin-input {
  height: 42px;
  font-size: 1.1rem;
  letter-spacing: 0.25em;
  text-align: center;
  background: #080d1a;
  border-color: rgba(245, 158, 11, 0.35);
}
.pin-input:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
}
.pin-input.pin-error {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3) !important;
  animation: pinShake 0.32s ease-in-out;
}

@keyframes pinShake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-6px); }
  40%, 80% { transform: translateX(6px); }
}

.pin-error-msg {
  font-size: 0.78rem;
  color: #fca5a5;
  font-weight: 600;
  text-align: center;
}

.confirm-subwarning {
  font-size: 0.76rem;
  color: #94a3b8;
  line-height: 1.35;
  border-top: 1px solid rgba(245, 158, 11, 0.15);
  padding-top: 6px;
}

.confirm-subwarning-plain {
  font-size: 0.8rem;
  color: #94a3b8;
}

.confirm-actions {
  display: flex;
  width: 100%;
  gap: 12px;
}
.confirm-btn-cancel {
  flex: 1;
  justify-content: center;
  padding: 10px 16px;
  font-size: 0.92rem;
}
.confirm-btn-delete {
  flex: 1;
  justify-content: center;
  padding: 10px 16px;
  font-size: 0.92rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
</style>
