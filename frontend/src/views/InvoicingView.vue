<template>
  <div class="invoicing-page animate-fade-in">
    <!-- Page Header & Toolbar -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Facturación Electrónica (CFDI 4.0)</h2>
        <p class="page-subtitle">Emisión fiscal con PAC certificado, timbrado digital y validación oficial del SAT</p>
      </div>
      <div class="toolbar">
        <div class="search-wrap">
          <span class="search-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </span>
          <input
            v-model="searchTerm"
            class="form-input"
            placeholder="Buscar por Folio, UUID o RFC..."
            style="width: 250px"
            @input="debouncedSearch"
          />
        </div>

        <select v-model="statusFilter" class="form-select" style="width: auto" @change="fetchInvoices(1)">
          <option value="">Todos los estatus</option>
          <option value="vigente">Solo Vigentes</option>
          <option value="cancelada">Solo Canceladas</option>
        </select>

        <button class="btn btn-primary" @click="openNewModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;display:inline-block">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Nueva Factura</span>
        </button>
      </div>
    </div>

    <!-- Alert / Toast Banner -->
    <div v-if="toastMessage" class="toast-alert animate-fade-in" :class="'toast-alert--' + toastType">
      <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;flex-shrink:0">
        <path v-if="toastType === 'success'" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        <path v-else d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Stats Grid using StatCard component -->
    <div class="stats-grid">
      <StatCard
        label="Facturado en el Mes (MXN)"
        :value="formatCurrency(metrics.total_invoiced_month)"
        icon="revenue"
        color="var(--accent)"
      />
      <StatCard
        label="Comprobantes Vigentes"
        :value="metrics.active_invoices_count"
        icon="profit"
        color="#10b981"
      />
      <StatCard
        label="IVA Trasladado (16%)"
        :value="formatCurrency(metrics.total_iva_month)"
        icon="margin"
        color="#8b5cf6"
      />
      <StatCard
        label="Comprobantes Cancelados"
        :value="metrics.cancelled_invoices_count"
        icon="alert"
        color="#f43f5e"
      />
    </div>

    <!-- Main Table Card: Full-Height Frame -->
    <div class="card invoicing-card" style="padding: 0; overflow: hidden">
      <!-- Loading State -->
      <div v-if="loading" class="empty-state">
        <div class="loading-spinner"></div>
        <span class="text-muted" style="font-size:0.875rem">Cargando comprobantes fiscales...</span>
      </div>

      <!-- Empty State -->
      <div v-else-if="invoices.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;margin-bottom:10px;color:var(--text-muted)">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <p class="font-semibold text-text" style="margin-bottom:4px">No se encontraron facturas</p>
        <p class="text-muted text-xs" style="margin-bottom:14px">Aún no hay comprobantes emitidos con los criterios de búsqueda actuales.</p>
        <button class="btn btn-secondary" style="font-size:0.8rem;padding:6px 14px" @click="openNewModal">
          Emitir Primera Factura
        </button>
      </div>

      <!-- Data Table with Full-Height Dynamic Scroll -->
      <div v-else class="table-scroll table-scroll-invoices">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 105px">Folio</th>
              <th style="width: 180px">Folio Fiscal (UUID)</th>
              <th>Cliente / Razón Social</th>
              <th style="width: 130px">RFC Receptor</th>
              <th style="width: 170px">Orden / Vehículo</th>
              <th style="width: 110px">Fecha</th>
              <th style="width: 120px" class="text-right">Total</th>
              <th style="width: 100px; text-align: center">Estado</th>
              <th style="width: 110px; text-align: right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id">
              <td>
                <span class="folio-tag">{{ inv.series }}-{{ String(inv.folio).padStart(4, '0') }}</span>
              </td>
              <td>
                <div class="uuid-wrap">
                  <span class="font-mono text-xs">{{ inv.uuid ? inv.uuid.substring(0, 13) + '...' : '—' }}</span>
                  <button class="copy-btn" title="Copiar UUID completo" @click="copyText(inv.uuid)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  </button>
                </div>
              </td>
              <td>
                <div class="customer-cell uppercase font-semibold" :title="inv.razon_social_receptor">
                  {{ inv.razon_social_receptor }}
                </div>
              </td>
              <td>
                <span class="rfc-badge font-mono">{{ inv.rfc_receptor }}</span>
              </td>
              <td>
                <span v-if="inv.order_id" class="text-xs text-muted">
                  Orden #{{ inv.order_id }} <span v-if="inv.order_vehicle" class="text-slate-400">({{ inv.order_vehicle }})</span>
                </span>
                <span v-else class="text-xs text-muted">Concepto libre</span>
              </td>
              <td class="text-xs text-muted font-mono">
                {{ formatDate(inv.fecha_timbrado) }}
              </td>
              <td class="text-right font-semibold text-accent font-mono">
                {{ formatCurrency(inv.total) }}
              </td>
              <td style="text-align: center">
                <span class="sat-status-pill" :class="inv.status === 'vigente' ? 'sat-status-pill--green' : 'sat-status-pill--red'">
                  {{ inv.status === 'vigente' ? 'Vigente' : 'Cancelada' }}
                </span>
              </td>
              <td>
                <div class="td-actions" style="justify-content: flex-end">
                  <button class="btn-icon" title="Ver Comprobante Oficial SAT" @click="viewInvoice(inv)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button class="btn-icon" title="Descargar XML CFDI 4.0" @click="downloadXml(inv)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  </button>
                  <button
                    v-if="inv.status === 'vigente'"
                    class="btn-icon del"
                    title="Cancelar Factura ante el SAT"
                    @click="openCancelModal(inv)"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Barra de Paginación y Selector de Cantidad -->
      <div class="table-pagination" v-if="invoices.length > 0">
        <div class="pagination-left">
          <div class="pagination-size-selector">
            <span>Mostrar:</span>
            <select v-model.number="perPage" class="pagination-size-select" @change="changePerPage">
              <option :value="10">10 facturas</option>
              <option :value="15">15 facturas</option>
              <option :value="25">25 facturas</option>
              <option :value="50">50 facturas</option>
            </select>
          </div>
          <span class="pagination-info">
            Mostrando <strong>{{ meta.total > 0 ? (meta.current_page - 1) * meta.per_page + 1 : 0 }}</strong> a <strong>{{ Math.min(meta.current_page * meta.per_page, meta.total) }}</strong> de <strong>{{ meta.total }}</strong> comprobantes
          </span>
        </div>

        <div class="pagination-pages" v-if="meta.last_page > 1">
          <button
            class="pagination-btn"
            :disabled="meta.current_page === 1"
            @click="goToPage(1)"
            title="Primera página"
          >
            «
          </button>
          <button
            class="pagination-btn"
            :disabled="meta.current_page === 1"
            @click="goToPage(meta.current_page - 1)"
            title="Página anterior"
          >
            ‹ Anterior
          </button>

          <template v-for="p in visiblePages" :key="p">
            <span v-if="p === '...'" class="pagination-ellipsis">...</span>
            <button
              v-else
              class="pagination-btn"
              :class="{ active: meta.current_page === p }"
              @click="goToPage(p)"
            >
              {{ p }}
            </button>
          </template>

          <button
            class="pagination-btn"
            :disabled="meta.current_page === meta.last_page"
            @click="goToPage(meta.current_page + 1)"
            title="Página siguiente"
          >
            Siguiente ›
          </button>
          <button
            class="pagination-btn"
            :disabled="meta.current_page === meta.last_page"
            @click="goToPage(meta.last_page)"
            title="Última página"
          >
            »
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <NewInvoiceModal
      :show="showNewModal"
      :available-orders="availableOrders"
      @close="showNewModal = false"
      @created="onInvoiceCreated"
    />

    <InvoiceDetailModal
      :show="showDetailModal"
      :invoice="selectedInvoice"
      @close="showDetailModal = false"
      @cancelled="openCancelModal"
    />

    <!-- Admin PIN Cancellation Modal -->
    <Modal v-model="showCancelPrompt" title="Cancelar Factura SAT">
      <div class="modal-body">
        <div class="cancel-warning-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;color:#f87171;flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <div class="cancel-warning-text">
            Se revocará el comprobante fiscal <strong>{{ invoiceToCancel?.series }}-{{ invoiceToCancel?.folio }}</strong> (UUID: {{ invoiceToCancel?.uuid }}). Esta acción es irreversible ante el SAT.
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Motivo de Cancelación SAT</label>
          <select v-model="cancelReason" class="form-select">
            <option value="02">02 - Comprobante emitido con errores sin relación</option>
            <option value="01">01 - Comprobante emitido con errores con relación</option>
            <option value="03">03 - No se llevó a cabo la operación</option>
            <option value="04">04 - Operación nominativa relacionada en una factura global</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">PIN de Administrador <span style="color:var(--danger)">*</span></label>
          <input
            v-model="adminPinInput"
            type="password"
            maxlength="8"
            class="form-input font-mono text-center"
            style="letter-spacing: 0.25em; font-size: 1.15rem"
            placeholder="••••"
            @keyup.enter="confirmCancel"
          />
        </div>

        <div v-if="cancelError" class="cancel-error-text">
          {{ cancelError }}
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-ghost" :disabled="cancelling" @click="showCancelPrompt = false">
          Volver
        </button>
        <button class="btn btn-danger" :disabled="!adminPinInput || cancelling" @click="confirmCancel">
          {{ cancelling ? 'Cancelando...' : 'Confirmar Cancelación Fiscal' }}
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
/**
 * @fileoverview Workshop Electronic Invoicing & SAT CFDI 4.0 Management View
 * @module views/InvoicingView
 * @description Manages Mexican fiscal CFDI 4.0 invoices for automotive repair orders:
 * - Server-side paginated querying and listing of stamped tax vouchers.
 * - Real-time filtering by SAT validity status (Vigente / Cancelada) and full-text search (Folio, UUID, RFC).
 * - Monthly fiscal KPI metric cards (Total Invoiced, Active Invoices, Transferred 16% IVA, Cancelled Vouchers).
 * - Modal workflows for CFDI 4.0 generation and detailed printable fiscal representation (PDF/Print engine).
 * - Administrator PIN-protected tax cancellation directly against the SAT authority.
 */

import { ref, reactive, computed, onMounted } from 'vue'
import apiClient from '../utils/apiClient'
import { formatCurrency, formatDate } from '../utils/format'
import StatCard from '../components/ui/StatCard.vue'
import Modal from '../components/ui/Modal.vue'
import NewInvoiceModal from '../components/invoicing/NewInvoiceModal.vue'
import InvoiceDetailModal from '../components/invoicing/InvoiceDetailModal.vue'

// =============================================================================
// REACTIVE COMPONENT STATE
// =============================================================================

/**
 * List of invoice records fetched from the backend API for the current page.
 * @type {import('vue').Ref<Array<Object>>}
 */
const invoices = ref([])

/**
 * Available customer work orders eligible for linking to new fiscal invoices.
 * @type {import('vue').Ref<Array<Object>>}
 */
const availableOrders = ref([])

/**
 * Indicates whether an asynchronous invoice query is currently in progress.
 * @type {import('vue').Ref<boolean>}
 */
const loading = ref(false)

/**
 * Real-time search query string for filtering by Folio, UUID, or RFC.
 * @type {import('vue').Ref<string>}
 */
const searchTerm = ref('')

/**
 * Active filter for SAT cancellation status ('vigente', 'cancelada', or empty string for all).
 * @type {import('vue').Ref<string>}
 */
const statusFilter = ref('')

/**
 * User feedback flash toast message text.
 * @type {import('vue').Ref<string>}
 */
const toastMessage = ref('')

/**
 * User feedback flash toast visual variant ('success' | 'error').
 * @type {import('vue').Ref<'success'|'error'>}
 */
const toastType = ref('success')

/**
 * Number of invoice rows displayed per pagination page.
 * @type {import('vue').Ref<number>}
 */
const perPage = ref(15)

/**
 * Server pagination metadata synchronized with API responses.
 * @type {Object}
 * @property {number} current_page - Current active page number
 * @property {number} last_page - Total number of available pages
 * @property {number} per_page - Number of records per page
 * @property {number} total - Total records matching active criteria
 */
const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

/**
 * Monthly aggregated fiscal metrics calculated by the backend for the active month.
 * @type {Object}
 * @property {number} total_invoiced_month - Total revenue invoiced in MXN
 * @property {number} active_invoices_count - Count of active/valid vouchers
 * @property {number} cancelled_invoices_count - Count of cancelled vouchers
 * @property {number} total_iva_month - Total 16% transferred IVA collected
 */
const metrics = reactive({
  total_invoiced_month: 0,
  active_invoices_count: 0,
  cancelled_invoices_count: 0,
  total_iva_month: 0,
})

// =============================================================================
// MODAL DIALOG STATE CONTROLLERS
// =============================================================================

/** Visibility state for the new invoice generation modal */
const showNewModal = ref(false)

/** Visibility state for the detailed fiscal voucher printable modal */
const showDetailModal = ref(false)

/** Currently inspected invoice record passed to the detail modal */
const selectedInvoice = ref(null)

// =============================================================================
// SAT CANCELLATION STATE (PROTECTED BY ADMIN PIN)
// =============================================================================

/** Visibility state for the Administrator PIN cancellation confirmation prompt */
const showCancelPrompt = ref(false)

/** Target invoice selected for fiscal cancellation */
const invoiceToCancel = ref(null)

/** Official SAT cancellation motive code (01, 02, 03, or 04) */
const cancelReason = ref('02')

/** Administrator security PIN entered for authorization */
const adminPinInput = ref('')

/** Indicates whether cancellation network call is currently in-flight */
const cancelling = ref(false)

/** Validation or API error message encountered during cancellation */
const cancelError = ref('')

// =============================================================================
// COMPUTED PROPERTIES
// =============================================================================

/**
 * Sliding window of page numbers and ellipsis markers for the pagination toolbar.
 * @type {import('vue').ComputedRef<Array<number|string>>}
 */
const visiblePages = computed(() => {
  const current = meta.current_page
  const total = meta.last_page
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  const pages = []
  pages.push(1)
  if (current > 3) pages.push('...')
  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

// =============================================================================
// COMPONENT METHODS & EVENT HANDLERS
// =============================================================================

/** Timer reference for search input debounce */
let searchDebounceTimer = null

/**
 * Debounces search input changes by 300ms to reduce redundant API queries.
 */
function debouncedSearch() {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    fetchInvoices(1)
  }, 300)
}

/**
 * Displays an ephemeral feedback toast alert message.
 * @param {string} message - Message text to present
 * @param {'success'|'error'} [type='success'] - Visual color scheme variant
 */
function showToast(message, type = 'success') {
  toastMessage.value = message
  toastType.value = type
  setTimeout(() => {
    toastMessage.value = ''
  }, 4000)
}

/**
 * Copies the provided string (e.g., Fiscal UUID or Folio) to the system clipboard.
 * @param {string} text - Text to copy
 */
function copyText(text) {
  if (!text) return
  navigator.clipboard.writeText(text)
  showToast('Folio Fiscal UUID copiado al portapapeles.')
}

/**
 * Fetches paginated invoices and aggregated monthly metrics from the backend API.
 * @param {number} [page=1] - Target page index to retrieve
 * @returns {Promise<void>}
 */
async function fetchInvoices(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('per_page', perPage.value)
    if (searchTerm.value) params.append('search', searchTerm.value)
    if (statusFilter.value) params.append('status', statusFilter.value)

    const res = await apiClient.get(`/invoices?${params.toString()}`)
    if (res && res.data) {
      invoices.value = res.data
      if (res.meta) {
        Object.assign(meta, res.meta)
      }
      if (res.metrics) {
        Object.assign(metrics, res.metrics)
      }
    }
  } catch (err) {
    showToast(err.message || 'Error al obtener facturas.', 'error')
  } finally {
    loading.value = false
  }
}

/**
 * Navigates to the designated page number within valid bounds.
 * @param {number} page - Destination page number
 */
function goToPage(page) {
  if (page < 1 || page > meta.last_page) return
  fetchInvoices(page)
}

/**
 * Resets pagination back to the first page when changing page size.
 */
function changePerPage() {
  fetchInvoices(1)
}

/**
 * Fetches available workshop repair orders from the backend to populate the invoice picker.
 * @returns {Promise<void>}
 */
async function fetchOrders() {
  try {
    const res = await apiClient.get('/orders')
    if (res) {
      availableOrders.value = res.data || res || []
    }
  } catch {
    // Non-blocking fallback
  }
}

/**
 * Opens the new CFDI 4.0 invoice generation modal and refreshes available orders.
 */
function openNewModal() {
  fetchOrders()
  showNewModal.value = true
}

/**
 * Callback invoked when a new invoice is successfully created and stamped.
 * Refreshes invoice list and automatically opens the printable detail representation.
 * @param {Object} newInv - Freshly stamped CFDI invoice model
 */
function onInvoiceCreated(newInv) {
  showNewModal.value = false
  showToast('Factura CFDI 4.0 emitida y timbrada exitosamente.')
  fetchInvoices(1)
  fetchOrders()
  if (newInv) {
    selectedInvoice.value = newInv
    showDetailModal.value = true
  }
}

/**
 * Opens the detailed printable voucher inspection modal for the selected invoice.
 * @param {Object} inv - Target invoice entity
 */
function viewInvoice(inv) {
  selectedInvoice.value = inv
  showDetailModal.value = true
}

/**
 * Opens a new browser tab to trigger the direct download of the certified SAT XML document.
 * @param {Object} inv - Target invoice entity
 */
function downloadXml(inv) {
  const url = `${import.meta.env.VITE_API_URL}/invoices/${inv.id}/xml`
  window.open(url, '_blank')
}

/**
 * Opens the Administrator PIN prompt to initiate a revocation request against the SAT.
 * @param {Object} inv - Target invoice entity to cancel
 */
function openCancelModal(inv) {
  showDetailModal.value = false
  invoiceToCancel.value = inv
  cancelReason.value = '02'
  adminPinInput.value = ''
  cancelError.value = ''
  showCancelPrompt.value = true
}

/**
 * Submits the fiscal cancellation request with Administrator PIN verification.
 * @returns {Promise<void>}
 */
async function confirmCancel() {
  if (!invoiceToCancel.value || !adminPinInput.value) return
  cancelling.value = true
  cancelError.value = ''

  try {
    await apiClient.post(
      `/invoices/${invoiceToCancel.value.id}/cancel`,
      { motivo: cancelReason.value },
      { headers: { 'X-Admin-Pin': adminPinInput.value.trim() } }
    )
    showCancelPrompt.value = false
    showToast('Factura cancelada exitosamente ante el SAT.', 'success')
    fetchInvoices(meta.current_page)
    fetchOrders()
  } catch (err) {
    cancelError.value = err.message || 'PIN incorrecto o error al cancelar la factura.'
  } finally {
    cancelling.value = false
  }
}

/**
 * Initial lifecycle hook: fetches initial page of invoices and orders.
 */
onMounted(() => {
  fetchInvoices(1)
  fetchOrders()
})
</script>

<style scoped>
/* ==============================================================================
   1. FULL-HEIGHT LAYOUT & FLEX CONTAINER ARCHITECTURE
   ============================================================================== */
.invoicing-page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - var(--header-h) - 4rem);
  min-height: 580px;
  gap: 1.25rem;
}

.invoicing-card {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.table-scroll-invoices {
  flex: 1;
  min-height: 0;
  max-height: none !important;
  overflow-y: auto;
  overflow-x: auto;
}

.invoicing-card .table-pagination {
  margin-top: auto;
  flex-shrink: 0;
}

/* ==============================================================================
   2. FLASH TOAST NOTIFICATIONS & EMPTY STATES
   ============================================================================== */
.toast-alert {
  padding: 10px 16px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  font-weight: 500;
  flex-shrink: 0;
}

.toast-alert--success {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34d399;
}

.toast-alert--error {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.empty-state {
  padding: 3rem 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
}

.loading-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(59, 130, 246, 0.2);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ==============================================================================
   3. TABLE TYPOGRAPHY, UUID & SAT STATUS BADGES
   ============================================================================== */
.folio-tag {
  font-family: monospace;
  font-weight: 700;
  font-size: 0.78rem;
  color: #60a5fa;
  background: rgba(37, 99, 235, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.25);
  padding: 3px 7px;
  border-radius: 6px;
}

.uuid-wrap {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.customer-cell {
  max-width: 220px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.82rem;
}

.rfc-badge {
  background: rgba(148, 163, 184, 0.1);
  color: #cbd5e1;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.75rem;
}

.copy-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 3px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.18s;
}

.copy-btn:hover {
  color: var(--accent);
  background: rgba(59, 130, 246, 0.15);
}

.sat-status-pill {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  display: inline-block;
}

.sat-status-pill--green {
  background: rgba(16, 185, 129, 0.16);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.sat-status-pill--red {
  background: rgba(239, 68, 68, 0.16);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

/* ==============================================================================
   4. CANCELLATION DIALOG & PIN AUTHORIZATION BOX
   ============================================================================== */
.cancel-warning-box {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.25);
  border-radius: var(--radius-sm);
  padding: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.cancel-warning-text {
  font-size: 0.82rem;
  color: #cbd5e1;
  line-height: 1.45;
}

.cancel-error-text {
  font-size: 0.8rem;
  color: #f87171;
  text-align: center;
  font-weight: 500;
  margin-top: 4px;
}
</style>
