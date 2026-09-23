<template>
  <header class="header">
    <div class="header-right">
      <div class="header-date">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="date-ic">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span>{{ formattedDate }}</span>
      </div>

      <!-- Selector de Rol: Operador vs Administrador -->
      <div class="role-badge-container">
        <!-- Modo Administrador Activo -->
        <div v-if="isAdmin" class="role-pill role-pill-admin" title="Modo Administrador activado">
          <span class="role-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:14px;height:14px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
          </span>
          <span class="role-name">Administrador</span>
          <button class="role-action-btn" @click="openChangePinModal" title="Cambiar PIN de seguridad">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Cambiar PIN</span>
          </button>
          <button class="role-exit-btn" @click="logoutAdmin" title="Cerrar sesión de administrador">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <span>Salir</span>
          </button>
        </div>

        <!-- Modo Operador (Normal) -->
        <div v-else class="role-pill role-pill-normal">
          <span class="role-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <span class="role-name">Operador</span>
          <button class="btn-soy-admin" @click="openAdminModal" title="Activar privilegios de administrador con PIN">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Administrador</span>
          </button>
        </div>
      </div>

      <div class="status-dot">
        <span class="dot"></span>
        <span class="status-text">Sistema activo</span>
      </div>
    </div>

    <!-- Modal Login Administrador -->
    <Modal v-model="showAdminModal">
      <template #header>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;border-radius:10px;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(245,158,11,0.2)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <div>
            <div style="font-size:1.15rem;font-weight:800;color:#ffffff">Acceso de Administrador</div>
            <div style="font-size:0.82rem;color:var(--text-muted)">Ingresa tu PIN de seguridad para habilitar permisos de alto valor</div>
          </div>
        </div>
      </template>
      <div class="modal-body" style="padding:1.5rem;display:flex;flex-direction:column;gap:14px">
        <div class="form-group">
          <label class="form-label">PIN de Seguridad *</label>
          <input
            ref="adminPinInputRef"
            v-model="adminPinInput"
            type="password"
            maxlength="8"
            placeholder="Introduce PIN de seguridad"
            class="form-input form-input-lg"
            style="letter-spacing:0.25em;font-size:1.15rem;text-align:center"
            @keyup.enter="handleAdminLogin"
          />
        </div>
      </div>
      <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:10px">
        <button class="btn btn-ghost" @click="showAdminModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary" @click="handleAdminLogin" type="button" style="background:#f59e0b;border-color:#f59e0b;color:#0b0f19;font-weight:700">
          Desbloquear Modo Admin
        </button>
      </div>
    </Modal>

    <!-- Modal Cambiar PIN de Administrador -->
    <Modal v-model="showChangePinModal">
      <template #header>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;border-radius:10px;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(245,158,11,0.2)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
          </div>
          <div>
            <div style="font-size:1.15rem;font-weight:800;color:#ffffff">Cambiar PIN de Administrador</div>
            <div style="font-size:0.82rem;color:var(--text-muted)">Actualiza el código numérico para autorizaciones y eliminaciones contables</div>
          </div>
        </div>
      </template>
      <div class="modal-body" style="padding:1.5rem;display:flex;flex-direction:column;gap:14px">
        <div class="form-group">
          <label class="form-label">PIN Actual *</label>
          <input
            v-model="changePinForm.currentPin"
            type="password"
            maxlength="8"
            placeholder="Introduce PIN actual"
            class="form-input form-input-lg"
            style="letter-spacing:0.25em;font-size:1.1rem;text-align:center"
            :class="{ 'input-has-error': changePinSubmitted && !changePinForm.currentPin }"
          />
          <span v-if="changePinSubmitted && !changePinForm.currentPin" class="form-field-error">
            El PIN actual es obligatorio
          </span>
        </div>

        <div class="form-group">
          <label class="form-label">Nuevo PIN (Mínimo 4 dígitos) *</label>
          <input
            v-model="changePinForm.newPin"
            type="password"
            maxlength="8"
            placeholder="Nuevo PIN numérico"
            class="form-input form-input-lg"
            style="letter-spacing:0.25em;font-size:1.1rem;text-align:center"
            :class="{ 'input-has-error': changePinSubmitted && (!changePinForm.newPin || changePinForm.newPin.length < 4) }"
          />
          <span v-if="changePinSubmitted && (!changePinForm.newPin || changePinForm.newPin.length < 4)" class="form-field-error">
            El nuevo PIN debe tener al menos 4 caracteres o dígitos
          </span>
        </div>

        <div class="form-group">
          <label class="form-label">Confirmar Nuevo PIN *</label>
          <input
            v-model="changePinForm.confirmPin"
            type="password"
            maxlength="8"
            placeholder="Repite el nuevo PIN"
            class="form-input form-input-lg"
            style="letter-spacing:0.25em;font-size:1.1rem;text-align:center"
            :class="{ 'input-has-error': changePinSubmitted && changePinForm.confirmPin !== changePinForm.newPin }"
            @keyup.enter="handleChangePin"
          />
          <span v-if="changePinSubmitted && changePinForm.confirmPin !== changePinForm.newPin" class="form-field-error">
            La confirmación no coincide con el nuevo PIN
          </span>
          <span class="field-hint" style="margin-top:4px;font-size:0.78rem;color:var(--text-muted)" v-else>
            El nuevo PIN sustituirá al anterior de inmediato para autorizar la baja de órdenes y egresos.
          </span>
        </div>
      </div>
      <div class="modal-footer" style="padding:1rem 1.5rem;display:flex;justify-content:flex-end;gap:10px">
        <button class="btn btn-ghost" @click="showChangePinModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary" @click="handleChangePin" :disabled="savingPin" type="button" style="background:#f59e0b;border-color:#f59e0b;color:#0b0f19;font-weight:700">
          {{ savingPin ? 'Guardando...' : 'Guardar Nuevo PIN' }}
        </button>
      </div>
    </Modal>
  </header>
</template>

<script setup>
/**
 * @fileoverview Application Header Component
 * @module components/layout/AppHeader
 * @description Top header bar showing localized current date, active user role indicator
 * (Operator vs. Administrator), PIN login authorization modal, PIN change workflow,
 * and system online status heartbeat.
 */

import { ref, computed, nextTick } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { useToast } from '../../composables/useToast'
import Modal from '../ui/Modal.vue'

/** Authentication composable methods and state */
const { isAdmin, loginAdmin, logoutAdmin, updatePin } = useAuth()

/** Centralized toast notification dispatcher */
const toast = useToast()

/** Controls visibility of the Admin PIN login modal */
const showAdminModal = ref(false)

/** Reactive PIN input value for logging in as admin */
const adminPinInput = ref('')

/** Input DOM reference for automatic focus management */
const adminPinInputRef = ref(null)

/** Controls visibility of the Change PIN modal */
const showChangePinModal = ref(false)

/** Validation flag tracking form submission attempt */
const changePinSubmitted = ref(false)

/** Form state container for updating the administrator PIN */
const changePinForm = ref({
  currentPin: '',
  newPin: '',
  confirmPin: ''
})

/**
 * Opens the administrator authentication modal and focuses the PIN input field.
 */
function openAdminModal() {
  adminPinInput.value = ''
  showAdminModal.value = true
  nextTick(() => adminPinInputRef.value?.focus())
}

/**
 * Validates the entered PIN and elevates current session to Administrator mode.
 */
async function handleAdminLogin() {
  const success = await loginAdmin(adminPinInput.value)
  if (success) {
    toast.success('Modo Administrador activado exitosamente.')
    showAdminModal.value = false
  } else {
    adminPinInput.value = ''
    toast.error('PIN incorrecto. No se pudo activar el modo Administrador.')
    nextTick(() => adminPinInputRef.value?.focus())
  }
}

/**
 * Opens the Change PIN modal dialog and clears form values.
 */
function openChangePinModal() {
  changePinSubmitted.value = false
  changePinForm.value = {
    currentPin: '',
    newPin: '',
    confirmPin: ''
  }
  showChangePinModal.value = true
}

/** Loading state during PIN modification API request */
const savingPin = ref(false)

/**
 * Validates PIN requirements and persists the new administrator security PIN.
 */
async function handleChangePin() {
  changePinSubmitted.value = true
  const { currentPin, newPin, confirmPin } = changePinForm.value

  if (!currentPin) {
    return toast.error('Ingresa el PIN actual.')
  }
  if (!newPin || newPin.trim().length < 4) {
    return toast.error('El nuevo PIN debe tener al menos 4 dígitos.')
  }
  if (newPin !== confirmPin) {
    return toast.error('La confirmación no coincide con el nuevo PIN.')
  }

  savingPin.value = true
  try {
    await updatePin(currentPin, newPin)
    toast.success('PIN de Administrador actualizado con éxito.')
    showChangePinModal.value = false
    changePinSubmitted.value = false
  } catch (err) {
    changePinForm.value.currentPin = ''
    toast.error(err.message || 'El PIN actual es incorrecto.')
  } finally {
    savingPin.value = false
  }
}

/**
 * Formats the current date into a human-friendly Mexican Spanish string.
 * @type {import('vue').ComputedRef<string>}
 */
const formattedDate = computed(() => {
  const now = new Date()
  const formatted = now.toLocaleDateString('es-MX', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
  return formatted.charAt(0).toUpperCase() + formatted.slice(1)
})
</script>

<style scoped>
.header {
  height: var(--header-h); min-height: var(--header-h);
  display: flex; align-items: center; justify-content: flex-end; gap: 16px;
  padding: 0 2rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-base);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  position: relative;
  z-index: 10;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.header-date {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
}
.date-ic {
  width: 14px;
  height: 14px;
  opacity: 0.7;
}

.status-dot {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-muted);
  background: rgba(16, 185, 129, 0.08);
  border: 1px solid rgba(16, 185, 129, 0.2);
  padding: 5px 12px;
  border-radius: 20px;
}
.dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--success);
  box-shadow: 0 0 8px var(--success);
  animation: pulse 2.4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}

/* Role Badges */
.role-badge-container {
  display: flex;
  align-items: center;
}
.role-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  transition: all 0.2s ease;
}
.role-pill-normal {
  background: rgba(148, 163, 184, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #cbd5e1;
}
.role-pill-admin {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.25));
  border: 1px solid rgba(245, 158, 11, 0.45);
  color: #fef08a;
  box-shadow: 0 0 12px rgba(245, 158, 11, 0.25);
  animation: adminGlow 3s infinite alternate ease-in-out;
}
@keyframes adminGlow {
  0% { box-shadow: 0 0 8px rgba(245, 158, 11, 0.2); }
  100% { box-shadow: 0 0 16px rgba(245, 158, 11, 0.4); }
}
.role-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}
.role-name {
  font-weight: 700;
}
.btn-soy-admin {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: rgba(37, 99, 235, 0.15);
  border: 1px solid rgba(56, 189, 248, 0.35);
  color: #93c5fd;
  border-radius: 12px;
  font-size: 0.74rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-left: 4px;
}
.btn-soy-admin:hover {
  background: var(--accent);
  color: #ffffff;
  border-color: var(--accent);
  box-shadow: 0 2px 8px var(--accent-glow);
  transform: translateY(-1px);
}
.role-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(245, 158, 11, 0.2);
  border: 1px solid rgba(245, 158, 11, 0.4);
  color: #fef08a;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
  margin-left: 4px;
}
.role-action-btn:hover {
  background: rgba(245, 158, 11, 0.35);
  color: #ffffff;
  border-color: rgba(245, 158, 11, 0.6);
  transform: translateY(-1px);
}
.role-exit-btn {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #fef3c7;
  padding: 2px 7px;
  border-radius: 10px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
  margin-left: 3px;
}
.role-exit-btn:hover {
  background: rgba(239, 68, 68, 0.25);
  color: #fecaca;
  border-color: rgba(239, 68, 68, 0.4);
}
.form-field-error {
  font-size: 0.78rem;
  color: #fca5a5;
  font-weight: 600;
  margin-top: 2px;
}
.input-has-error {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3) !important;
  background: rgba(239, 68, 68, 0.05) !important;
}
</style>