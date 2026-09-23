<template>
  <Modal :model-value="show" wide @update:model-value="$emit('close')">
    <template #header>
      <div class="modal-custom-header">
        <div class="modal-header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;display:block">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        </div>
        <div>
          <h3 class="modal-custom-title">Emitir Factura Electrónica (CFDI 4.0)</h3>
          <p class="modal-custom-subtitle">Timbrado con PAC certificado y validación oficial del SAT</p>
        </div>
      </div>
    </template>

    <div class="modal-body invoice-modal-body">
      <!-- Error Alert -->
      <div v-if="errorMessage" class="sat-alert sat-alert--error animate-fade-in">
        <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;flex-shrink:0">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Step 1: Concept Origin -->
      <div class="form-panel">
        <div class="panel-header">
          <span class="step-num">1</span>
          <span class="panel-title">Origen de los Conceptos Facturables</span>
        </div>

        <div class="mode-selector">
          <button
            type="button"
            class="mode-card"
            :class="{ 'mode-card--active': mode === 'order' }"
            @click="mode = 'order'"
          >
            <div class="mode-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:block">
                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
              </svg>
            </div>
            <div class="mode-card-info">
              <div class="mode-card-title">Vincular Orden de Trabajo</div>
              <div class="mode-card-desc">Toma refacciones y mano de obra de una orden registrada</div>
            </div>
          </button>

          <button
            type="button"
            class="mode-card"
            :class="{ 'mode-card--active': mode === 'manual' }"
            @click="mode = 'manual'"
          >
            <div class="mode-card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:block">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </div>
            <div class="mode-card-info">
              <div class="mode-card-title">Captura Libre de Servicio</div>
              <div class="mode-card-desc">Ingresa o busca servicios/refacciones de catálogo</div>
            </div>
          </button>
        </div>

        <!-- Mode A: Order Selection with Smart Search & Dropdown Filter -->
        <div v-if="mode === 'order'" style="margin-top:14px">
          <label class="field-label">
            Seleccionar Orden de Trabajo del Taller <span class="req-star">*</span>
          </label>

          <!-- Selected Order State Display -->
          <div v-if="selectedOrder" class="selected-order-card animate-fade-in" :class="{ 'selected-order-card--invoiced': selectedOrder.is_invoiced }">
            <div class="selected-order-left">
              <div class="selected-order-badges">
                <span class="folio-tag">Orden #{{ selectedOrder.id }}</span>
                <span class="order-status-tag" :class="'order-status--' + selectedOrder.status">
                  {{ formatOrderStatus(selectedOrder.status) }}
                </span>
                <span v-if="selectedOrder.is_invoiced" class="order-invoiced-tag">
                  ⚠️ Ya facturada ({{ selectedOrder.active_invoice_folio }})
                </span>
              </div>
              <div class="selected-order-name">
                {{ selectedOrder.customer_name }}
              </div>
              <div class="selected-order-vehicle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline-block;vertical-align:-2px">
                  <rect x="1" y="3" width="15" height="13"/>
                  <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                  <circle cx="5.5" cy="18.5" r="2.5"/>
                  <circle cx="18.5" cy="18.5" r="2.5"/>
                </svg>
                <span>{{ selectedOrder.vehicle || 'Vehículo sin especificar' }}</span>
              </div>
            </div>
            <div class="selected-order-right">
              <div class="selected-order-total-label">Total Facturable</div>
              <div class="selected-order-total-amount font-mono">{{ formatCurrency(selectedOrder.total) }} MXN</div>
              <button type="button" class="btn btn-secondary btn-sm" @click="clearSelectedOrder" title="Cambiar a otra orden de trabajo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;display:inline-block">
                  <polyline points="23 4 23 10 17 10"/>
                  <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                <span>Cambiar orden</span>
              </button>
            </div>
          </div>

          <!-- Alert banner if order is already invoiced -->
          <div v-if="selectedOrder && selectedOrder.is_invoiced" class="invoiced-alert-banner animate-fade-in">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="invoiced-alert-icon">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div class="invoiced-alert-body">
              <strong>¡Acción no permitida! La Orden #{{ selectedOrder.id }} ya cuenta con la factura vigente {{ selectedOrder.active_invoice_folio }}</strong>
              <p>Por normatividad fiscal del SAT (CFDI 4.0), no es posible emitir dos comprobantes vigentes para la misma orden de trabajo. Si requieres volver a facturar esta reparación con datos fiscales diferentes, primero debes cancelar el comprobante anterior en el listado de Facturación.</p>
            </div>
          </div>

          <!-- Search Input & Interactive Dropdown -->
          <div v-else class="order-search-container" ref="orderSearchContainerRef">
            <div class="search-input-wrap">
              <span class="search-ic">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;display:block">
                  <circle cx="11" cy="11" r="8"/>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
              </span>
              <input
                ref="orderSearchInputRef"
                v-model="orderSearchTerm"
                type="text"
                class="form-input search-input"
                placeholder="Escribe # de orden (ej. 39), cliente, auto o placas..."
                @focus="orderDropdownOpen = true"
                @input="orderDropdownOpen = true"
                @keydown.down.prevent="navigateOrderResults(1)"
                @keydown.up.prevent="navigateOrderResults(-1)"
                @keydown.enter.prevent="selectHighlightedOrder"
                @keydown.esc="orderDropdownOpen = false"
              />
              <button
                v-if="orderSearchTerm"
                type="button"
                class="search-clear-btn"
                title="Limpiar búsqueda"
                @click="orderSearchTerm = ''; orderDropdownOpen = true; $refs.orderSearchInputRef?.focus()"
              >
                ×
              </button>
            </div>

            <!-- Dropdown Results Menu -->
            <div v-if="orderDropdownOpen" class="order-dropdown-menu">
              <div class="dropdown-header-bar">
                <span>{{ filteredOrders.length }} orden(es) encontrada(s)</span>
                <span v-if="orderSearchTerm" class="text-xs text-muted">Filtro: "{{ orderSearchTerm }}"</span>
              </div>

              <div class="dropdown-list-scroll">
                <div
                  v-for="(ord, idx) in filteredOrders"
                  :key="ord.id"
                  class="order-dropdown-item"
                  :class="{ 'order-dropdown-item--focused': highlightedOrderIndex === idx }"
                  @mouseenter="highlightedOrderIndex = idx"
                  @click="selectOrder(ord)"
                >
                  <div class="item-left">
                    <div class="item-top">
                      <span class="folio-tag">#{{ ord.id }}</span>
                      <strong class="item-customer">{{ ord.customer_name }}</strong>
                      <span class="order-status-tag" :class="'order-status--' + ord.status">
                        {{ formatOrderStatus(ord.status) }}
                      </span>
                      <span v-if="ord.is_invoiced" class="order-invoiced-tag">
                        Facturada ({{ ord.active_invoice_folio }})
                      </span>
                    </div>
                    <div class="item-vehicle">
                      {{ ord.vehicle || 'Sin vehículo' }}
                      <span v-if="ord.notes" class="item-notes"> — {{ ord.notes.substring(0, 55) }}...</span>
                    </div>
                  </div>
                  <div class="item-right">
                    <span class="item-total font-mono">{{ formatCurrency(ord.total) }} MXN</span>
                  </div>
                </div>

                <div v-if="filteredOrders.length === 0" class="dropdown-empty-state">
                  No se encontraron órdenes que coincidan con "{{ orderSearchTerm }}".
                </div>
              </div>
            </div>

            <div class="field-hint-msg">
              💡 Busca y selecciona la orden del cliente para tomar sus refacciones y mano de obra automáticamente.
            </div>
          </div>
        </div>

        <!-- Mode B: Manual Concept with Catalog Autocomplete / Search -->
        <div v-else class="manual-concept-section" style="margin-top:14px">
          <div class="manual-concept-grid">
            <div class="col-desc" ref="productSearchContainerRef">
              <div class="field-header-row">
                <label class="field-label">Descripción del Concepto <span class="req-star">*</span></label>
                <span v-if="catalogProducts.length > 0" class="text-xs text-muted" style="font-size:0.7rem">
                  🔍 Sugerencias automáticas de catálogo
                </span>
              </div>
              <div class="product-search-wrap">
                <input
                  v-model="form.custom_concept.descripcion"
                  type="text"
                  class="form-input"
                  placeholder="Ej. Servicio de afinación mayor, cambio de balatas, etc."
                  @focus="productDropdownOpen = true"
                  @input="productDropdownOpen = true"
                  @keydown.esc="productDropdownOpen = false"
                />
                <!-- Auto-suggest dropdown from workshop catalog -->
                <div v-if="productDropdownOpen && filteredCatalogProducts.length > 0" class="product-dropdown-menu">
                  <div class="dropdown-header-bar">
                    <span>Servicios y refacciones de catálogo ({{ filteredCatalogProducts.length }})</span>
                  </div>
                  <div class="dropdown-list-scroll">
                    <div
                      v-for="prod in filteredCatalogProducts"
                      :key="prod.id"
                      class="product-dropdown-item"
                      @click="selectCatalogProduct(prod)"
                    >
                      <div class="prod-item-info">
                        <span class="prod-type-tag" :class="prod.is_service ? 'prod-type--service' : 'prod-type--part'">
                          {{ prod.is_service ? 'Servicio' : 'Refacción' }}
                        </span>
                        <strong class="prod-name">{{ prod.name }}</strong>
                      </div>
                      <div class="prod-price font-mono text-accent">
                        {{ formatCurrency(prod.price) }} MXN
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-amount">
              <label class="field-label">Importe antes de IVA ($ MXN) <span class="req-star">*</span></label>
              <input
                v-model.number="form.custom_concept.valor_unitario"
                type="number"
                step="0.01"
                min="1"
                class="form-input font-mono"
                placeholder="1000.00"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Customer Fiscal Data (SAT CFDI 4.0) -->
      <div class="form-panel">
        <div class="panel-header">
          <span class="step-num">2</span>
          <span class="panel-title">Datos Fiscales del Cliente (Receptor)</span>
        </div>

        <div class="fiscal-grid">
          <!-- RFC Input -->
          <div class="fiscal-field">
            <div class="field-header-row">
              <label class="field-label">
                RFC Receptor <span class="req-star">*</span>
              </label>
              <span v-if="isRfcValid" class="rfc-status rfc-status--valid">✓ Formato SAT válido</span>
              <span v-else-if="form.rfc_receptor" class="rfc-status rfc-status--invalid">✗ Formato inválido</span>
            </div>
            <div class="rfc-input-wrap">
              <input
                v-model="form.rfc_receptor"
                type="text"
                maxlength="13"
                class="form-input font-mono uppercase"
                :class="{ 'input--valid': isRfcValid, 'input--invalid': form.rfc_receptor && !isRfcValid }"
                placeholder="XAXX010101000"
                @input="form.rfc_receptor = form.rfc_receptor.toUpperCase()"
              />
              <button
                type="button"
                class="rfc-quick-btn"
                title="Autocompletar con RFC Genérico Oficial de Público en General"
                @click="fillPublicoGeneral"
              >
                Público General
              </button>
            </div>
            <span v-if="form.rfc_receptor && !isRfcValid" class="field-error-msg">
              El RFC debe tener 12 (Moral) o 13 caracteres (Física) con formato SAT oficial.
            </span>
          </div>

          <!-- Razón Social -->
          <div class="fiscal-field">
            <label class="field-label">
              Razón Social / Nombre Fiscal <span class="req-star">*</span>
            </label>
            <input
              v-model="form.razon_social_receptor"
              type="text"
              class="form-input uppercase"
              placeholder="NOMBRE O DENOMINACIÓN SOCIAL REGISTRADA ANTE EL SAT"
              @input="form.razon_social_receptor = form.razon_social_receptor.toUpperCase()"
            />
          </div>

          <!-- Código Postal -->
          <div class="fiscal-field">
            <div class="field-header-row">
              <label class="field-label">
                Código Postal Fiscal <span class="req-star">*</span>
              </label>
              <span v-if="form.codigo_postal_receptor.length === 5" class="rfc-status rfc-status--valid">✓ 5 dígitos</span>
            </div>
            <input
              v-model="form.codigo_postal_receptor"
              type="text"
              maxlength="5"
              class="form-input font-mono"
              placeholder="06000"
              @input="form.codigo_postal_receptor = form.codigo_postal_receptor.replace(/\D/g, '').slice(0, 5)"
            />
          </div>

          <!-- Régimen Fiscal -->
          <div class="fiscal-field">
            <label class="field-label">
              Régimen Fiscal <span class="req-star">*</span>
            </label>
            <select v-model="form.regimen_fiscal_receptor" class="form-select">
              <option value="">-- Seleccionar Régimen SAT --</option>
              <option v-for="reg in catalogs.regimenes_fiscales" :key="reg.code" :value="reg.code">
                {{ reg.name }}
              </option>
            </select>
          </div>

          <!-- Uso de CFDI -->
          <div class="fiscal-field">
            <label class="field-label">
              Uso de CFDI <span class="req-star">*</span>
            </label>
            <select v-model="form.uso_cfdi" class="form-select">
              <option value="">-- Seleccionar Uso de CFDI --</option>
              <option v-for="uso in catalogs.usos_cfdi" :key="uso.code" :value="uso.code">
                {{ uso.name }}
              </option>
            </select>
          </div>

          <!-- Forma de Pago -->
          <div class="fiscal-field">
            <label class="field-label">
              Forma de Pago <span class="req-star">*</span>
            </label>
            <select v-model="form.forma_pago" class="form-select">
              <option v-for="fp in catalogs.formas_pago" :key="fp.code" :value="fp.code">
                {{ fp.name }}
              </option>
            </select>
          </div>

          <!-- Método de Pago -->
          <div class="fiscal-field col-span-2">
            <label class="field-label">Método de Pago <span class="req-star">*</span></label>
            <div class="metodo-radio-grid">
              <label
                v-for="mp in catalogs.metodos_pago"
                :key="mp.code"
                class="radio-pill"
                :class="{ 'radio-pill--active': form.metodo_pago === mp.code }"
              >
                <input type="radio" v-model="form.metodo_pago" :value="mp.code" style="display:none" />
                <span class="radio-pill-code">{{ mp.code }}</span>
                <span class="radio-pill-label">{{ mp.name }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Tax & Financial Breakdown -->
      <div class="financials-card">
        <div class="financials-row">
          <span class="financials-label">Subtotal (Importe gravado):</span>
          <span class="financials-val font-mono">{{ formatCurrency(calculatedSubtotal) }} MXN</span>
        </div>
        <div class="financials-row">
          <span class="financials-label">Impuesto Trasladado (IVA 16%):</span>
          <span class="financials-val financials-val--tax font-mono">+ {{ formatCurrency(calculatedIva) }} MXN</span>
        </div>
        <div class="financials-divider"></div>
        <div class="financials-row financials-row--total">
          <span class="financials-total-label">Total CFDI 4.0:</span>
          <span class="financials-total-val font-mono text-accent">{{ formatCurrency(calculatedTotal) }} MXN</span>
        </div>
      </div>

      <!-- SAT Pac Notice -->
      <div class="sat-notice-box">
        <div class="sat-notice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:block">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 16v-4"/>
            <path d="M12 8h.01"/>
          </svg>
        </div>
        <div class="sat-notice-text">
          <strong>Certificación PAC SAT v4.0:</strong> Se generará el Folio Fiscal UUID v4, sellos criptográficos SHA-256 de emisor y SAT, cadena original y código QR de verificación fiscal.
        </div>
      </div>
    </div>

    <!-- Modal Footer Actions (Pinned to Bottom) -->
    <div class="modal-footer">
      <div v-if="validationHint" class="validation-hint-pill" :title="validationHint">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ validationHint }}</span>
      </div>
      <div class="footer-btn-group">
        <button type="button" class="btn btn-ghost" :disabled="submitting" @click="$emit('close')">
          Cancelar
        </button>
        <button
          type="button"
          class="btn btn-primary"
          :disabled="!canSubmit || submitting"
          @click="handleSubmit"
        >
          <svg v-if="submitting" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="loading-spin" style="width:15px;height:15px;display:inline-block">
            <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"/>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:15px;height:15px;display:inline-block">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
          <span>{{ submitting ? 'Conectando con PAC y Timbrando...' : 'Emitir y Timbrar Factura' }}</span>
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
/**
 * @fileoverview Electronic Invoicing (CFDI 4.0) Issuance & PAC Stamping Modal
 * @module components/invoicing/NewInvoiceModal
 * @description Provides an interactive wizard for issuing Mexican fiscal CFDI 4.0 invoices:
 * - Two issuance modalities: Linking an existing Workshop Work Order or Custom Line Item.
 * - Anti-duplicate invoice validation preventing double billing on previously invoiced orders.
 * - Interactive keyboard-accessible search dropdown for quick order and catalog lookup.
 * - Real-time RFC formatting, regex verification, and "Público General" (XAXX010101000) quick-fill.
 * - Real-time SAT tax engine breakdown calculating IVA-included subtotal, transferred tax, and total.
 * - Preloads official SAT fiscal catalogs (Regímenes Fiscales, Usos de CFDI, Formas y Métodos de Pago).
 * - Direct asynchronous submission to PAC certification endpoint with comprehensive error handling.
 */

import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import Modal from '../ui/Modal.vue'
import apiClient from '../../utils/apiClient'
import { formatCurrency } from '../../utils/format'

/**
 * Component Props
 * @property {boolean} show - Controls dialog visibility
 * @property {Array<Object>} availableOrders - Collection of repair orders available for invoice linking
 */
const props = defineProps({
  show: { type: Boolean, default: false },
  availableOrders: { type: Array, default: () => [] },
})

/**
 * Component Emits
 * @fires close - Dispatched when the user closes or cancels the modal
 * @fires created - Dispatched when the invoice is stamped by PAC, providing the new invoice data
 */
const emit = defineEmits(['close', 'created'])

// =============================================================================
// MODAL WORKFLOW & FORM MODE
// =============================================================================

/** Active issuance modality: 'order' (linked work order) or 'manual' (ad-hoc service) */
const mode = ref('order')

/** Indicates whether the PAC stamping network request is currently in-flight */
const submitting = ref(false)

/** Flash error message returned from PAC or backend validation */
const errorMessage = ref('')

// =============================================================================
// WORK ORDER SEARCH & AUTOCOMPLETE STATE
// =============================================================================

/** Text query for filtering available repair orders */
const orderSearchTerm = ref('')

/** Visibility of the order autocomplete dropdown menu */
const orderDropdownOpen = ref(false)

/** Keyboard highlighted index in the order dropdown list */
const highlightedOrderIndex = ref(-1)

/** DOM template reference to the order search wrapper for click-outside detection */
const orderSearchContainerRef = ref(null)

/** DOM template reference to the order search input element */
const orderSearchInputRef = ref(null)

// =============================================================================
// CATALOG & PRODUCT SEARCH STATE (MANUAL MODE)
// =============================================================================

/** Catalog products and services available for manual line items */
const catalogProducts = ref([])

/** Visibility of the manual concept product catalog dropdown */
const productDropdownOpen = ref(false)

/** DOM template reference to the product search container */
const productSearchContainerRef = ref(null)

// =============================================================================
// SAT FISCAL CATALOGS & FORM MODEL
// =============================================================================

/**
 * Official SAT Catalogs fetched from the backend API.
 * @type {Object}
 * @property {Array<Object>} regimenes_fiscales - SAT Tax Regimes (c_RegimenFiscal)
 * @property {Array<Object>} usos_cfdi - SAT CFDI Usages (c_UsoCFDI)
 * @property {Array<Object>} formas_pago - SAT Payment Forms (c_FormaPago)
 * @property {Array<Object>} metodos_pago - SAT Payment Methods (c_MetodoPago)
 */
const catalogs = reactive({
  regimenes_fiscales: [],
  usos_cfdi: [],
  formas_pago: [],
  metodos_pago: [],
})

/**
 * Main form payload for CFDI 4.0 issuance.
 * @type {Object}
 */
const form = reactive({
  order_id: null,
  rfc_receptor: '',
  razon_social_receptor: '',
  codigo_postal_receptor: '',
  regimen_fiscal_receptor: '612',
  uso_cfdi: 'G03',
  forma_pago: '03',
  metodo_pago: 'PUE',
  custom_concept: {
    descripcion: 'Servicio de afinación mayor automotriz y mantenimiento',
    cantidad: 1,
    valor_unitario: 1500,
  },
})

/**
 * Official SAT RFC Regular Expression Pattern (supports both Persona Física 13 chars and Moral 12 chars).
 * @constant {RegExp}
 */
const RFC_REGEX = /^([A-ZÑ&]{3,4})(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01]))([A-Z\d]{2}[A\d])$/i

// =============================================================================
// COMPUTED VALIDATIONS & DERIVED DATA
// =============================================================================

/**
 * Checks whether the receptor RFC string satisfies the official SAT syntax.
 * @type {import('vue').ComputedRef<boolean>}
 */
const isRfcValid = computed(() => {
  return RFC_REGEX.test(form.rfc_receptor.trim())
})

/**
 * Currently selected order entity based on form.order_id.
 * @type {import('vue').ComputedRef<Object|null>}
 */
const selectedOrder = computed(() => {
  if (!form.order_id) return null
  return props.availableOrders.find(o => o.id === form.order_id) || null
})

/**
 * Fast multi-attribute filtering of customer orders by ID, client, vehicle, or notes.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
const filteredOrders = computed(() => {
  const list = props.availableOrders || []
  const q = orderSearchTerm.value.trim().toLowerCase()
  if (!q) {
    return list.slice(0, 15)
  }
  const cleanQ = q.replace(/^#/, '')
  return list.filter(o => {
    const idMatch = String(o.id) === cleanQ || String(o.id).includes(cleanQ)
    const customerMatch = o.customer_name && o.customer_name.toLowerCase().includes(q)
    const vehicleMatch = o.vehicle && o.vehicle.toLowerCase().includes(q)
    const notesMatch = o.notes && o.notes.toLowerCase().includes(q)
    return idMatch || customerMatch || vehicleMatch || notesMatch
  }).slice(0, 20)
})

/**
 * Filtered catalog products matching current manual concept description or SKU.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
const filteredCatalogProducts = computed(() => {
  const q = form.custom_concept.descripcion ? form.custom_concept.descripcion.trim().toLowerCase() : ''
  if (!q) return catalogProducts.value.slice(0, 8)
  return catalogProducts.value.filter(p => {
    return p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q))
  }).slice(0, 10)
})

/**
 * Net taxable subtotal derived from the gross price with IVA included (price / 1.16).
 * @type {import('vue').ComputedRef<number>}
 */
const calculatedSubtotal = computed(() => {
  if (mode.value === 'order' && selectedOrder.value) {
    return Math.round((selectedOrder.value.total / 1.16) * 100) / 100
  }
  if (mode.value === 'manual') {
    return Math.round((form.custom_concept.valor_unitario * form.custom_concept.cantidad) * 100) / 100
  }
  return 0
})

/**
 * Transferred 16% IVA calculated from the net taxable subtotal (subtotal * 0.16).
 * @type {import('vue').ComputedRef<number>}
 */
const calculatedIva = computed(() => {
  return Math.round(calculatedSubtotal.value * 0.16 * 100) / 100
})

/**
 * Total fiscal invoice amount in MXN (subtotal + IVA).
 * @type {import('vue').ComputedRef<number>}
 */
const calculatedTotal = computed(() => {
  return Math.round((calculatedSubtotal.value + calculatedIva.value) * 100) / 100
})

/**
 * Explanatory validation hint guiding the user on missing or invalid requirements.
 * Returns null if all SAT fiscal validations pass cleanly.
 * @type {import('vue').ComputedRef<string|null>}
 */
const validationHint = computed(() => {
  if (mode.value === 'order' && !form.order_id) {
    return 'Busca y selecciona una orden de trabajo para facturar.'
  }
  if (mode.value === 'order' && selectedOrder.value?.is_invoiced) {
    return `La Orden #${selectedOrder.value.id} ya cuenta con la factura vigente ${selectedOrder.value.active_invoice_folio || ''}. Cancela la factura previa para poder emitir una nueva.`
  }
  if (!form.rfc_receptor.trim()) {
    return 'Ingresa el RFC del receptor o usa "Público General".'
  }
  if (!isRfcValid.value) {
    return 'El RFC ingresado no tiene formato oficial SAT válido.'
  }
  if (!form.razon_social_receptor.trim()) {
    return 'Ingresa la Razón Social o Nombre Fiscal.'
  }
  if (!form.codigo_postal_receptor.trim() || form.codigo_postal_receptor.length !== 5) {
    return 'El Código Postal fiscal debe ser de 5 dígitos numéricos.'
  }
  if (!form.regimen_fiscal_receptor) {
    return 'Selecciona el Régimen Fiscal del receptor.'
  }
  if (!form.uso_cfdi) {
    return 'Selecciona el Uso de CFDI.'
  }
  if (mode.value === 'manual') {
    if (!form.custom_concept.descripcion.trim()) return 'Ingresa la descripción del servicio.'
    if (!form.custom_concept.valor_unitario || form.custom_concept.valor_unitario <= 0) return 'El importe debe ser mayor a $0.'
  }
  return null
})

/**
 * Determines whether the invoice can be submitted for PAC stamping.
 * @type {import('vue').ComputedRef<boolean>}
 */
const canSubmit = computed(() => {
  return validationHint.value === null
})

// =============================================================================
// COMPONENT ACTIONS & HANDLERS
// =============================================================================

/**
 * Returns a human-readable label in Spanish for a given work order status code.
 * @param {string} status - Internal order status ('open'|'in_progress'|'done'|'delivered')
 * @returns {string} Localized status label
 */
function formatOrderStatus(status) {
  const map = {
    open: 'Abierta',
    in_progress: 'En proceso',
    done: 'Terminada',
    delivered: 'Entregada',
  }
  return map[status] || status || 'Registrada'
}

/**
 * Assigns the selected order to the form model and populates recipient defaults.
 * @param {Object} ord - Work order entity
 */
function selectOrder(ord) {
  form.order_id = ord.id
  orderDropdownOpen.value = false
  orderSearchTerm.value = ''
  onOrderSelect()
}

/**
 * Clears current order selection and refocuses search input.
 */
function clearSelectedOrder() {
  form.order_id = null
  orderSearchTerm.value = ''
  orderDropdownOpen.value = true
  nextTick(() => {
    orderSearchInputRef.value?.focus()
  })
}

/**
 * Navigates order autocomplete dropdown with keyboard up/down arrows.
 * @param {number} direction - 1 for down, -1 for up
 */
function navigateOrderResults(direction) {
  if (!orderDropdownOpen.value) {
    orderDropdownOpen.value = true
    return
  }
  const count = filteredOrders.value.length
  if (count === 0) return
  highlightedOrderIndex.value = (highlightedOrderIndex.value + direction + count) % count
}

/**
 * Selects currently keyboard-highlighted order on Enter key press.
 */
function selectHighlightedOrder() {
  if (highlightedOrderIndex.value >= 0 && highlightedOrderIndex.value < filteredOrders.value.length) {
    selectOrder(filteredOrders.value[highlightedOrderIndex.value])
  } else if (filteredOrders.value.length === 1) {
    selectOrder(filteredOrders.value[0])
  }
}

/**
 * Populates manual concept fields with data from selected product catalog item.
 * @param {Object} prod - Product catalog entity
 */
function selectCatalogProduct(prod) {
  form.custom_concept.descripcion = prod.name
  form.custom_concept.valor_unitario = parseFloat(prod.price || 0)
  productDropdownOpen.value = false
}

/**
 * Populates customer name as uppercase business name when order is picked.
 */
function onOrderSelect() {
  if (selectedOrder.value) {
    if (!form.razon_social_receptor || form.razon_social_receptor === 'PUBLICO EN GENERAL') {
      form.razon_social_receptor = selectedOrder.value.customer_name.toUpperCase()
    }
  }
}

/**
 * Quick-fill preset for generic customer sales ("Público en General" CFDI 4.0).
 * Conforms to SAT official guidelines (RFC: XAXX010101000, Regimen: 616, Uso: S01).
 */
function fillPublicoGeneral() {
  form.rfc_receptor = 'XAXX010101000'
  form.razon_social_receptor = 'PUBLICO EN GENERAL'
  form.regimen_fiscal_receptor = '616'
  form.uso_cfdi = 'S01'
  form.codigo_postal_receptor = '06000'
}

/**
 * Loads official SAT fiscal catalogs from the backend endpoint.
 * @returns {Promise<void>}
 */
async function loadCatalogs() {
  try {
    const res = await apiClient.get('/invoices/catalogs')
    if (res && res.data) {
      catalogs.regimenes_fiscales = res.data.regimenes_fiscales || []
      catalogs.usos_cfdi = res.data.usos_cfdi || []
      catalogs.formas_pago = res.data.formas_pago || []
      catalogs.metodos_pago = res.data.metodos_pago || []
    }
  } catch (err) {
    console.error('Error cargando catálogos SAT:', err)
  }
}

/**
 * Loads workshop products and services catalog for manual concept selection.
 * @returns {Promise<void>}
 */
async function loadProducts() {
  try {
    const res = await apiClient.get('/products')
    if (res) {
      catalogProducts.value = Array.isArray(res) ? res : (res.data || [])
    }
  } catch (err) {
    console.error('Error cargando catálogo de productos:', err)
  }
}

/**
 * Global document click listener for dismissing autocomplete dropdowns on outside click.
 * @param {MouseEvent} e - Mouse click event
 */
function handleDocumentClick(e) {
  if (orderSearchContainerRef.value && !orderSearchContainerRef.value.contains(e.target)) {
    orderDropdownOpen.value = false
  }
  if (productSearchContainerRef.value && !productSearchContainerRef.value.contains(e.target)) {
    productDropdownOpen.value = false
  }
}

/**
 * Submits the CFDI 4.0 issuance payload to the backend for certified PAC stamping.
 * @returns {Promise<void>}
 */
async function handleSubmit() {
  if (!canSubmit.value) return
  submitting.value = true
  errorMessage.value = ''

  try {
    const payload = {
      order_id: mode.value === 'order' ? form.order_id : null,
      rfc_receptor: form.rfc_receptor.trim().toUpperCase(),
      razon_social_receptor: form.razon_social_receptor.trim().toUpperCase(),
      codigo_postal_receptor: form.codigo_postal_receptor.trim(),
      regimen_fiscal_receptor: form.regimen_fiscal_receptor,
      uso_cfdi: form.uso_cfdi,
      forma_pago: form.forma_pago,
      metodo_pago: form.metodo_pago,
      custom_concept: mode.value === 'manual' ? form.custom_concept : null,
    }

    const res = await apiClient.post('/invoices', payload)
    emit('created', res.data)
  } catch (err) {
    errorMessage.value = err.message || 'Error al emitir y timbrar el comprobante fiscal.'
  } finally {
    submitting.value = false
  }
}

/**
 * Lifecycle hook: preloads catalogs, products, and attaches document click listener.
 */
onMounted(() => {
  loadCatalogs()
  loadProducts()
  document.addEventListener('click', handleDocumentClick)
})

/**
 * Lifecycle hook: cleans up document click listener.
 */
onUnmounted(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<style scoped>
/* ========================================================================= */
/* 1. Modal Custom Header & Base Shell Layout                               */
/* ========================================================================= */
.modal-custom-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-header-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(37, 99, 235, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #60a5fa;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-custom-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.modal-custom-subtitle {
  font-size: 0.78rem;
  color: var(--text-muted);
  margin: 2px 0 0 0;
}

.invoice-modal-body {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 1.4rem 1.75rem;
}

/* ========================================================================= */
/* 2. Step Form Panels & Mode Selector (Order vs Manual)                     */
/* ========================================================================= */
.form-panel {
  background: rgba(15, 23, 42, 0.45);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 14px 16px;
}

.panel-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.step-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--accent);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.panel-title {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text);
}

.mode-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.mode-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: rgba(15, 23, 42, 0.6);
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.mode-card:hover {
  background: var(--bg-hover);
  color: var(--text);
  border-color: rgba(148, 163, 184, 0.25);
}

.mode-card--active {
  background: rgba(37, 99, 235, 0.15);
  border-color: var(--accent);
  color: #60a5fa;
}

.mode-card-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.04);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: inherit;
  transition: all 0.2s;
}

.mode-card--active .mode-card-icon {
  background: rgba(37, 99, 235, 0.25);
  color: #60a5fa;
}

.mode-card-info {
  flex: 1;
  min-width: 0;
}

.mode-card-title {
  font-size: 0.82rem;
  font-weight: 600;
  color: inherit;
}

.mode-card-desc {
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-top: 2px;
  line-height: 1.35;
}

.field-label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 5px;
}

.field-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}

.field-header-row .field-label {
  margin-bottom: 0;
}

.req-star {
  color: #f87171;
  font-weight: bold;
}

/* ========================================================================= */
/* Selected Order Display Card                                               */
/* ========================================================================= */
.selected-order-card {
  padding: 12px 16px;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(59, 130, 246, 0.35);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.selected-order-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.selected-order-badges {
  display: flex;
  align-items: center;
  gap: 8px;
}

.selected-order-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.selected-order-vehicle {
  font-size: 0.76rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 5px;
}

.selected-order-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  flex-shrink: 0;
}

.selected-order-total-label {
  font-size: 0.68rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.selected-order-total-amount {
  font-size: 1.15rem;
  font-weight: 800;
  color: #34d399;
}

/* ========================================================================= */
/* Searchable Orders Combobox & Dropdown                                     */
/* ========================================================================= */
.order-search-container {
  position: relative;
}

.search-input-wrap {
  position: relative;
  width: 100%;
}

.search-input-wrap .search-input {
  padding-left: 38px;
  padding-right: 32px;
}

.search-input-wrap .search-ic {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  pointer-events: none;
}

.search-clear-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 1.1rem;
  cursor: pointer;
  padding: 2px 6px;
  line-height: 1;
  border-radius: 4px;
}

.search-clear-btn:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.1);
}

.order-dropdown-menu {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #0d1527;
  border: 1px solid rgba(59, 130, 246, 0.35);
  border-radius: var(--radius-sm);
  box-shadow: 0 14px 34px rgba(0, 0, 0, 0.85);
  z-index: 100;
  overflow: hidden;
}

.dropdown-header-bar {
  padding: 7px 12px;
  background: rgba(30, 41, 59, 0.7);
  border-bottom: 1px solid var(--border);
  font-size: 0.72rem;
  color: var(--text-muted);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-list-scroll {
  max-height: 240px;
  overflow-y: auto;
}

.order-dropdown-item {
  padding: 10px 14px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  cursor: pointer;
  transition: background 0.15s ease;
}

.order-dropdown-item:last-child {
  border-bottom: none;
}

.order-dropdown-item:hover,
.order-dropdown-item--focused {
  background: rgba(37, 99, 235, 0.18);
}

.item-left {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.item-top {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.item-customer {
  font-size: 0.84rem;
  color: var(--text);
}

.item-vehicle {
  font-size: 0.74rem;
  color: var(--text-muted);
}

.item-notes {
  color: #64748b;
  font-style: italic;
}

.item-right {
  flex-shrink: 0;
  text-align: right;
}

.item-total {
  font-weight: 700;
  font-size: 0.88rem;
  color: #38bdf8;
}

.dropdown-empty-state {
  padding: 16px;
  text-align: center;
  font-size: 0.78rem;
  color: var(--text-muted);
}

.order-status-tag {
  font-size: 0.66rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 9999px;
  text-transform: uppercase;
}

.order-status--done {
  background: rgba(16, 185, 129, 0.18);
  color: #34d399;
}

.order-status--delivered {
  background: rgba(59, 130, 246, 0.18);
  color: #60a5fa;
}

.order-status--in_progress {
  background: rgba(245, 158, 11, 0.18);
  color: #fbbf24;
}

.order-status--open {
  background: rgba(148, 163, 184, 0.18);
  color: #cbd5e1;
}

.order-invoiced-tag {
  font-size: 0.66rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 9999px;
  background: rgba(245, 158, 11, 0.18);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.4);
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.selected-order-card--invoiced {
  border-color: rgba(239, 68, 68, 0.4) !important;
  background: rgba(239, 68, 68, 0.05) !important;
}

.invoiced-alert-banner {
  margin-top: 10px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #fca5a5;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  font-size: 0.8rem;
  line-height: 1.45;
}

.invoiced-alert-icon {
  width: 18px;
  height: 18px;
  color: #ef4444;
  flex-shrink: 0;
  margin-top: 2px;
}

.invoiced-alert-body strong {
  display: block;
  color: #fecaca;
  font-size: 0.85rem;
  margin-bottom: 3px;
}

.invoiced-alert-body p {
  margin: 0;
  color: #cbd5e1;
  font-size: 0.76rem;
}

.field-hint-msg {
  font-size: 0.72rem;
  color: var(--text-muted);
  margin-top: 5px;
}

/* ========================================================================= */
/* Product Catalog Search & Autocomplete                                     */
/* ========================================================================= */
.product-search-wrap {
  position: relative;
}

.product-dropdown-menu {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #0d1527;
  border: 1px solid rgba(59, 130, 246, 0.35);
  border-radius: var(--radius-sm);
  box-shadow: 0 14px 34px rgba(0, 0, 0, 0.85);
  z-index: 100;
  overflow: hidden;
}

.product-dropdown-item {
  padding: 9px 12px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  cursor: pointer;
  transition: background 0.15s ease;
}

.product-dropdown-item:last-child {
  border-bottom: none;
}

.product-dropdown-item:hover {
  background: rgba(37, 99, 235, 0.18);
}

.prod-item-info {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.prod-name {
  font-size: 0.8rem;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.prod-type-tag {
  font-size: 0.64rem;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: 4px;
  text-transform: uppercase;
  flex-shrink: 0;
}

.prod-type--service {
  background: rgba(139, 92, 246, 0.18);
  color: #a78bfa;
}

.prod-type--part {
  background: rgba(59, 130, 246, 0.18);
  color: #60a5fa;
}

.prod-price {
  font-size: 0.8rem;
  font-weight: 700;
  flex-shrink: 0;
}

.manual-concept-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 12px;
}

/* ========================================================================= */
/* Fiscal Grid & Controls                                                    */
/* ========================================================================= */
.fiscal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.fiscal-field {
  display: flex;
  flex-direction: column;
}

.col-span-2 {
  grid-column: span 2;
}

.rfc-input-wrap {
  position: relative;
  width: 100%;
}

.rfc-input-wrap .form-input {
  padding-right: 125px;
}

.rfc-quick-btn {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(37, 99, 235, 0.18);
  border: 1px solid rgba(59, 130, 246, 0.35);
  color: #60a5fa;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 4px 9px;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}

.rfc-quick-btn:hover {
  background: var(--accent);
  color: #ffffff;
}

.rfc-status {
  font-size: 0.72rem;
  font-weight: 600;
}

.rfc-status--valid {
  color: #34d399;
}

.rfc-status--invalid {
  color: #f87171;
}

.field-error-msg {
  font-size: 0.72rem;
  color: #f87171;
  margin-top: 4px;
}

.input--valid {
  border-color: #10b981 !important;
}

.input--invalid {
  border-color: #ef4444 !important;
}

.metodo-radio-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.radio-pill {
  display: flex;
  flex-direction: column;
  padding: 8px 12px;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s;
  user-select: none;
}

.radio-pill:hover {
  background: var(--bg-hover);
  border-color: rgba(148, 163, 184, 0.25);
}

.radio-pill--active {
  border-color: var(--accent);
  background: rgba(37, 99, 235, 0.14);
}

.radio-pill-code {
  font-size: 0.82rem;
  font-weight: 700;
  color: #60a5fa;
}

.radio-pill-label {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.financials-card {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(59, 130, 246, 0.25);
  border-radius: var(--radius-sm);
  padding: 12px 16px;
}

.financials-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  margin-bottom: 4px;
}

.financials-label {
  color: var(--text-muted);
}

.financials-val {
  color: #e2e8f0;
}

.financials-val--tax {
  color: #34d399;
}

.financials-divider {
  height: 1px;
  background: var(--border);
  margin: 6px 0;
}

.financials-row--total {
  margin-bottom: 0;
  padding-top: 4px;
}

.financials-total-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #f8fafc;
}

.financials-total-val {
  font-size: 1.25rem;
  font-weight: 800;
}

.sat-notice-box {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: rgba(30, 58, 138, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.25);
  border-radius: var(--radius-sm);
}

.sat-notice-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sat-notice-text {
  font-size: 0.75rem;
  color: #cbd5e1;
  line-height: 1.4;
}

.sat-alert {
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.82rem;
}

.sat-alert--error {
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.validation-hint-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #f59e0b;
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.25);
  padding: 4px 10px;
  border-radius: 9999px;
  max-width: 380px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.footer-btn-group {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
}

.loading-spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
  .mode-selector,
  .fiscal-grid,
  .manual-concept-grid {
    grid-template-columns: 1fr;
  }
  .col-span-2 {
    grid-column: span 1;
  }
  .validation-hint-pill {
    display: none;
  }
  .selected-order-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .selected-order-right {
    align-items: flex-start;
    width: 100%;
    border-top: 1px solid var(--border);
    padding-top: 8px;
  }
}
</style>
