<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Administración & Finanzas</h2>
        <p class="page-subtitle">Control de ingresos por servicios de taller y registro de egresos operativos</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Registrar Egreso</button>
    </div>

    <!-- Stat cards de flujo de caja -->
    <div class="stats-grid">
      <StatCard
        icon="revenue"
        label="Ingresos por Servicios"
        :value="formatCurrency(store.totalRevenue)"
        color="var(--success)"
      />
      <StatCard
        icon="expenses"
        label="Gastos Operativos"
        :value="formatCurrency(store.totalExpenses)"
        color="var(--danger)"
      />
      <StatCard
        :icon="store.netProfit >= 0 ? 'profit' : 'expenses'"
        :label="store.netProfit >= 0 ? 'Utilidad Neta del Taller' : 'Déficit / Pérdida Neta'"
        :value="formatCurrency(store.netProfit)"
        :color="store.netProfit >= 0 ? 'var(--success)' : 'var(--danger)'"
      />
      <StatCard
        icon="margin"
        label="Margen Operativo"
        :value="(store.marginPct >= 0 ? '+' : '') + store.marginPct.toFixed(1) + '%'"
        :color="store.marginPct >= 0 ? 'var(--blue)' : 'var(--danger)'"
      />
    </div>

    <!-- Card de egresos -->
    <div class="card" style="padding:0">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <div class="search-wrap">
            <span class="search-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input
              v-model="search"
              class="form-input"
              placeholder="Buscar concepto o folio..."
              style="width: 250px"
            />
          </div>
          <select v-model="selectedCategory" class="form-select" style="width: auto">
            <option value="">Todas las categorías</option>
            <option value="refacciones">Refacciones e Insumos</option>
            <option value="herramientas">Herramientas y Equipo</option>
            <option value="nomina">Nómina de Mecánicos</option>
            <option value="renta">Renta del Taller</option>
            <option value="servicios">Servicios (Luz/Agua/Net)</option>
            <option value="otros">Otros Gastos</option>
          </select>
        </div>
        <div class="text-muted" style="font-size: 0.85rem">
          {{ filteredExpenses.length }} gastos registrados
        </div>
      </div>

      <div class="table-scroll table-scroll-accounting" v-if="filteredExpenses.length">
        <table class="data-table">
          <thead>
            <tr>
              <SortableTh label="Concepto" field="concept" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <SortableTh label="Categoría" field="category" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <SortableTh label="Método" field="payment_method" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <SortableTh label="Folio/Ref" field="reference" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <SortableTh label="Fecha" field="expense_date" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <SortableTh label="Monto" field="amount" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in pagedExpenses" :key="e.id">
              <td class="font-semibold">{{ e.concept }}</td>
              <td>
                <span class="cat-pill" :class="'cat-' + e.category">
                  {{ categoryLabel(e.category) }}
                </span>
              </td>
              <td><span class="pay-badge">{{ e.payment_method || 'efectivo' }}</span></td>
              <td><code>{{ e.reference || '—' }}</code></td>
              <td class="text-muted" style="font-size:0.84rem">{{ formatDate(e.expense_date) }}</td>
              <td class="font-semibold text-danger tabular-nums">-{{ formatCurrency(e.amount) }}</td>
              <td>
                <div class="td-actions">
                  <button
                    class="btn-icon del"
                    @click="remove(e)"
                    title="Eliminar Registro de Egreso"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
        </div>
        <p>{{ search || selectedCategory ? 'No hay egresos con los filtros aplicados' : 'No hay gastos registrados aún. Agrega el primero.' }}</p>
      </div>

      <!-- Barra de Paginación y Selector de Cantidad -->
      <div class="table-pagination" v-if="filteredExpenses.length">
        <div class="pagination-left">
          <div class="pagination-size-selector">
            <span>Mostrar:</span>
            <select v-model.number="pageSize" class="pagination-size-select">
              <option :value="10">10 registros</option>
              <option :value="20">20 registros</option>
              <option :value="50">50 registros</option>
              <option :value="100">100 registros</option>
            </select>
          </div>
          <span class="pagination-info">
            Mostrando <strong>{{ startRecord }}</strong> a <strong>{{ endRecord }}</strong> de <strong>{{ filteredExpenses.length }}</strong> gastos
          </span>
        </div>

        <div class="pagination-pages" v-if="totalPages > 1">
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="currentPage = 1"
            title="Primera página"
          >
            «
          </button>
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="currentPage--"
            title="Página anterior"
          >
            ‹ Anterior
          </button>

          <template v-for="p in visiblePages" :key="p">
            <span v-if="p === '...'" class="pagination-ellipsis">...</span>
            <button
              v-else
              class="pagination-btn"
              :class="{ active: currentPage === p }"
              @click="currentPage = p"
            >
              {{ p }}
            </button>
          </template>

          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
            title="Página siguiente"
          >
            Siguiente ›
          </button>
          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage = totalPages"
            title="Última página"
          >
            »
          </button>
        </div>
      </div>
    </div>

    <!-- Suite de Analítica & Estadísticas Animadas -->
    <FinancialAnalyticsSection />

    <!-- Modal Registrar Egreso -->
    <Modal v-model="showModal" :wide="true">
      <template #header>
        <div class="expense-modal-header">
          <div class="expense-modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px">
              <path d="M14 2H6a2 2 0 0 0-2 2v16l3-1.5 3 1.5 3-1.5 3 1.5 3-1.5 3 1.5V4a2 2 0 0 0-2-2z"/>
              <line x1="9" y1="9" x2="15" y2="9"/>
              <line x1="9" y1="13" x2="15" y2="13"/>
              <line x1="9" y1="17" x2="13" y2="17"/>
            </svg>
          </div>
          <div>
            <div class="expense-modal-title">Registrar Nuevo Egreso del Taller</div>
            <div class="expense-modal-subtitle">Asentamiento contable de gastos operativos, insumos, nómina y servicios</div>
          </div>
        </div>
      </template>

      <div class="modal-body expense-modal-body">
        <!-- Panel 1: Concepto y Clasificación del Gasto -->
        <div class="expense-panel">
          <div class="expense-panel-header">
            <span class="expense-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
              Concepto y Clasificación del Gasto
            </span>
            <span class="req-legend"><span class="req-star">*</span> Campos obligatorios</span>
          </div>

          <div class="form-group">
            <label class="form-label">
              Concepto o Justificación del Gasto <span class="req-star">*</span>
            </label>
            <input
              v-model="form.concept"
              class="form-input form-input-lg"
              :class="{ 'input-has-error': submitted && !form.concept.trim() }"
              placeholder="Ej. Compra lote 20 filtros de aceite a Distribuidora Sur, Pago de nómina..."
            />
            <span v-if="submitted && !form.concept.trim()" class="form-field-error">
              El concepto o justificación del gasto es obligatorio
            </span>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                Categoría Operativa <span class="req-star">*</span>
              </label>
              <select v-model="form.category" class="form-select form-input-lg">
                <option value="refacciones">Refacciones e Insumos</option>
                <option value="herramientas">Herramientas y Equipo</option>
                <option value="nomina">Nómina de Mecánicos</option>
                <option value="renta">Renta del Taller</option>
                <option value="servicios">Servicios (Luz / Agua / Internet)</option>
                <option value="otros">Otros Gastos Operativos</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">
                Folio / Factura o Ticket <span class="form-label-hint">(Opcional)</span>
              </label>
              <input
                v-model="form.reference"
                class="form-input form-input-lg"
                placeholder="Ej. FAC-8921, TK-4412 o Folio interno"
              />
              <span class="field-hint">Referencia física o comprobante para auditoría.</span>
            </div>
          </div>
        </div>

        <!-- Panel 2: Desglose Financiero y Forma de Pago -->
        <div class="expense-panel">
          <div class="expense-panel-header">
            <span class="expense-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              Importe Financiero y Dispersión
            </span>
            <span class="form-label-hint">Asiento contable directo</span>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">
                Monto del Egreso ($) <span class="req-star">*</span>
              </label>
              <div class="currency-input-wrap">
                <span class="currency-prefix">$</span>
                <input
                  :value="displayAmount"
                  type="text"
                  inputmode="decimal"
                  class="form-input form-input-lg currency-field"
                  :class="{ 'input-has-error': (submitted && (!form.amount || form.amount <= 0)) || (form.amount && form.amount > MAX_EXPENSE_LIMIT) }"
                  placeholder="0.00"
                  maxlength="10"
                  @keydown="handleAmountKeydown"
                  @input="handleAmountInput"
                  @paste="handleAmountPaste"
                />
                <span class="currency-suffix">MXN</span>
              </div>
              <span v-if="submitted && (!form.amount || form.amount <= 0)" class="form-field-error">
                Ingresa un monto válido mayor a $0.00
              </span>
              <span v-else-if="form.amount && form.amount > MAX_EXPENSE_LIMIT" class="form-field-error">
                Tope excedido: Máximo $1,000,000.00 MXN por transacción.
              </span>
              <span v-else class="field-hint">
                Máx. $1,000,000.00 MXN (Control interno anti-error de dedo / ISO 9001).
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Método de Liquidación <span class="req-star">*</span>
              </label>
              <select v-model="form.payment_method" class="form-select form-input-lg">
                <option value="transferencia">Transferencia SPEI</option>
                <option value="efectivo">Efectivo en Caja</option>
                <option value="tarjeta">Tarjeta Débito / Crédito</option>
              </select>
              <div v-if="form.payment_method === 'efectivo' && form.amount > 2000" class="fiscal-tip">
                Aviso SAT (Art. 27 LISR): Egresos en efectivo mayores a $2,000 no son deducibles.
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Fecha de Egreso <span class="req-star">*</span>
              </label>
              <input
                v-model="form.expense_date"
                type="date"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submitted && !form.expense_date }"
              />
              <span v-if="submitted && !form.expense_date" class="form-field-error">
                La fecha del egreso es requerida
              </span>
            </div>
          </div>
        </div>

        <!-- Banner de Impacto Contable en Caja -->
        <div class="expense-impact-banner">
          <div class="impact-info">
            <div class="impact-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                <polyline points="17 18 23 18 23 12"/>
              </svg>
              <span>Impacto en Flujo de Efectivo</span>
            </div>
            <span class="impact-subtitle">
              Se asentará como costo operativo bajo <strong>{{ categoryLabel(form.category) }}</strong> vía <strong>{{ paymentMethodLabel(form.payment_method) }}</strong>
            </span>
          </div>
          <div class="impact-amount-box">
            <span class="impact-currency">MXN</span>
            <span class="impact-value">-{{ formatCurrency(form.amount || 0) }}</span>
          </div>
        </div>
      </div>

      <div class="modal-footer expense-modal-footer">
        <button class="btn btn-ghost" @click="showModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary btn-submit-expense" @click="save" :disabled="loading" type="button">
          <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>{{ loading ? 'Asentando egreso...' : 'Registrar Egreso' }}</span>
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
/**
 * @fileoverview Workshop Accounting & Operational Expenses View
 * @module views/AccountingView
 * @description Manages the financial ledger of the auto shop, displaying real-time
 * cash flow metrics (Revenue, Operating Costs, Net Profit, Operating Margin),
 * expense registration modal, search and category filtering, sorting, and protected deletion.
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'
import StatCard from '../components/ui/StatCard.vue'
import Modal from '../components/ui/Modal.vue'
import SortableTh from '../components/ui/SortableTh.vue'
import { formatCurrency, formatDate } from '../utils/format'
import FinancialAnalyticsSection from '../components/analytics/FinancialAnalyticsSection.vue'

/** Global Pinia store instance */
const store = useStore()

/** Toast notification dispatcher */
const toast = useToast()

/** Centralized confirmation modal service */
const { askConfirm } = useConfirm()

/**
 * Initializes view data by fetching work orders, products, and operational expenses.
 */
onMounted(async () => {
  await Promise.all([
    store.fetchOrders(),
    store.fetchExpenses(),
    store.fetchProducts()
  ])
})

/** Search filter input */
const search = ref('')

/** Category filter selection */
const selectedCategory = ref('')

/** Active sort column field */
const sortField = ref('expense_date')

/** Active sort direction ('asc' or 'desc') */
const sortOrder = ref('desc')

/** Visibility toggle for the new expense modal */
const showModal = ref(false)

/** Async submission loading state */
const loading = ref(false)

/**
 * Toggles column sort field and direction.
 * @param {string} field - Selected property to sort by
 */
function handleSort(field) {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = field === 'expense_date' || field === 'amount' ? 'desc' : 'asc'
  }
}

/** ISO formatted date for default today date */
const today = new Date().toISOString().split('T')[0]

/**
 * Factory for creating an empty expense form payload.
 * @returns {Object} Fresh expense model
 */
const empty = () => ({
  concept: '',
  category: 'refacciones',
  amount: null,
  payment_method: 'transferencia',
  reference: '',
  expense_date: today,
})

/** Reactive form model for expense creation */
const form = ref(empty())

/** Form submission attempt flag for reactive inline validation feedback */
const submitted = ref(false)

/** Maximum allowable single transaction expense ceiling ($1,000,000.00 MXN) */
const MAX_EXPENSE_LIMIT = 1000000

/** String buffer for numeric input to smoothly retain decimals and prevent browser quirks */
const amountInput = ref('')

/** Computed display value for the currency input */
const displayAmount = computed(() => amountInput.value)

/**
 * Filtered and sorted collection of operational expenses.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
const filteredExpenses = computed(() => {
  const q = search.value.toLowerCase().trim()
  let list = store.expenses.filter((e) => {
    const matchQ =
      !q ||
      (e.concept && e.concept.toLowerCase().includes(q)) ||
      (e.reference && e.reference.toLowerCase().includes(q))
    const matchCat = !selectedCategory.value || e.category === selectedCategory.value
    return matchQ && matchCat
  })

  return list.sort((a, b) => {
    let valA = a[sortField.value]
    let valB = b[sortField.value]
    if (sortField.value === 'amount') {
      valA = parseFloat(valA || 0)
      valB = parseFloat(valB || 0)
      return sortOrder.value === 'asc' ? valA - valB : valB - valA
    } else if (sortField.value === 'expense_date') {
      const timeA = new Date(valA || 0).getTime()
      const timeB = new Date(valB || 0).getTime()
      return sortOrder.value === 'asc' ? timeA - timeB : timeB - timeA
    } else {
      valA = String(valA || '')
      valB = String(valB || '')
      const cmp = valA.localeCompare(valB, 'es', { numeric: true, sensitivity: 'base' })
      return sortOrder.value === 'asc' ? cmp : -cmp
    }
  })
})

/** Selected pagination page size (10, 20, 50, 100) */
const pageSize = ref(10)

/** Active page number (1-indexed) */
const currentPage = ref(1)

/** Reset active page to 1 whenever filters or page size change */
watch([search, selectedCategory, pageSize], () => {
  currentPage.value = 1
})

/** Total number of pagination pages */
const totalPages = computed(() => {
  return Math.ceil(filteredExpenses.value.length / pageSize.value) || 1
})

/** Slice of expenses for current pagination page */
const pagedExpenses = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredExpenses.value.slice(start, start + pageSize.value)
})

/** Start record number in current slice */
const startRecord = computed(() => {
  return filteredExpenses.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
})

/** End record number in current slice */
const endRecord = computed(() => {
  return Math.min(currentPage.value * pageSize.value, filteredExpenses.value.length)
})

/** Visible page buttons sequence with ellipsis support */
const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  if (current <= 4) {
    return [1, 2, 3, 4, 5, '...', total]
  }
  if (current >= total - 3) {
    return [1, '...', total - 4, total - 3, total - 2, total - 1, total]
  }
  return [1, '...', current - 1, current, current + 1, '...', total]
})

/**
 * Maps category code identifiers to localized UI badge text.
 * @param {string} cat - Category key
 * @returns {string} Human-readable label
 */
function categoryLabel(cat) {
  const map = {
    refacciones: 'Refacciones',
    herramientas: 'Herramientas',
    nomina: 'Nómina',
    renta: 'Renta',
    servicios: 'Servicios',
    otros: 'Otros',
  }
  return map[cat] || cat
}

/**
 * Maps payment method identifiers to human-readable labels.
 * @param {string} method - Payment method key
 * @returns {string} Human-readable label
 */
function paymentMethodLabel(method) {
  const map = {
    transferencia: 'Transferencia SPEI',
    efectivo: 'Efectivo en Caja',
    tarjeta: 'Tarjeta Débito / Crédito',
  }
  return map[method] || method
}

/**
 * Intercepts keyboard events to strictly permit numeric digits (0-9) and a single decimal point.
 * Blocks negative signs (-), plus signs (+), scientific notation (e/E), and invalid symbols.
 * @param {KeyboardEvent} e - Keydown event
 */
function handleAmountKeydown(e) {
  // Reject +, -, e, E, comma, and space
  if (['+', '-', 'e', 'E', ',', ' '].includes(e.key)) {
    e.preventDefault()
    return
  }

  // Allow navigation and editing control keys
  const allowedKeys = [
    'Backspace',
    'Delete',
    'Tab',
    'Escape',
    'Enter',
    'ArrowLeft',
    'ArrowRight',
    'ArrowUp',
    'ArrowDown',
    'Home',
    'End',
  ]
  if (allowedKeys.includes(e.key)) return

  // Allow standard clipboard and selection keyboard shortcuts (Ctrl/Cmd + A, C, V, X, Z)
  if (e.ctrlKey || e.metaKey) return

  // Decimal point validation: only allow one dot
  if (e.key === '.') {
    if (amountInput.value.includes('.')) {
      e.preventDefault()
    }
    return
  }

  // Reject any non-digit character
  if (!/^[0-9]$/.test(e.key)) {
    e.preventDefault()
  }
}

/**
 * Sanitizes input value to enforce pure numeric decimal format and cap at operational maximum.
 * Prevents "fat-finger" errors according to ISO 9001 and financial risk controls.
 * @param {InputEvent} event - Input change event
 */
function handleAmountInput(event) {
  let val = event.target.value.replace(/[^0-9.]/g, '')

  // Enforce single decimal point
  const parts = val.split('.')
  if (parts.length > 2) {
    val = parts[0] + '.' + parts.slice(1).join('')
  }

  // Limit decimals to 2 places (cents)
  if (parts.length === 2 && parts[1].length > 2) {
    val = parts[0] + '.' + parts[1].slice(0, 2)
  }

  // Prevent multiple leading zeroes
  if (parts[0].length > 1 && parts[0].startsWith('0') && !parts[0].startsWith('0.')) {
    val = String(parseInt(parts[0], 10)) + (parts.length > 1 ? '.' + parts[1] : '')
  }

  if (val === '') {
    amountInput.value = ''
    form.value.amount = null
    event.target.value = ''
    return
  }

  let num = parseFloat(val)
  if (isNaN(num)) {
    amountInput.value = ''
    form.value.amount = null
    event.target.value = ''
    return
  }

  // Enforce maximum operational ceiling ($1,000,000.00 MXN)
  if (num > MAX_EXPENSE_LIMIT) {
    num = MAX_EXPENSE_LIMIT
    val = String(MAX_EXPENSE_LIMIT)
    toast.warning(`Tope máximo de $${MAX_EXPENSE_LIMIT.toLocaleString('es-MX')} MXN aplicado por control interno.`)
  }

  amountInput.value = val
  form.value.amount = num
  event.target.value = val
}

/**
 * Sanitizes pasted clipboard data to ensure only pure numeric content is injected.
 * @param {ClipboardEvent} e - Clipboard paste event
 */
function handleAmountPaste(e) {
  e.preventDefault()
  const text = (e.clipboardData || window.clipboardData).getData('text') || ''
  let clean = text.replace(/[^0-9.]/g, '')
  const parts = clean.split('.')
  if (parts.length > 2) {
    clean = parts[0] + '.' + parts.slice(1).join('')
  }
  if (parts.length === 2 && parts[1].length > 2) {
    clean = parts[0] + '.' + parts[1].slice(0, 2)
  }

  if (!clean) return

  let num = parseFloat(clean)
  if (isNaN(num)) return

  if (num > MAX_EXPENSE_LIMIT) {
    num = MAX_EXPENSE_LIMIT
    clean = String(MAX_EXPENSE_LIMIT)
    toast.warning(`Tope máximo de $${MAX_EXPENSE_LIMIT.toLocaleString('es-MX')} MXN aplicado por control interno.`)
  }

  amountInput.value = clean
  form.value.amount = num
}

/**
 * Opens the expense creation modal with fresh default values.
 */
function openCreate() {
  submitted.value = false
  form.value = empty()
  amountInput.value = ''
  showModal.value = true
}

/**
 * Validates and submits a new expense record to the backend ledger.
 */
async function save() {
  submitted.value = true
  if (!form.value.concept.trim()) {
    return toast.error('El concepto o justificación del gasto es requerido')
  }
  if (!form.value.amount || form.value.amount <= 0) {
    return toast.error('Ingresa un monto de egreso válido mayor a $0.00')
  }
  if (form.value.amount > MAX_EXPENSE_LIMIT) {
    return toast.error(`El monto no puede exceder el límite de $${MAX_EXPENSE_LIMIT.toLocaleString('es-MX')} MXN`)
  }
  if (!form.value.expense_date) {
    return toast.error('La fecha del egreso es requerida')
  }
  loading.value = true
  try {
    await store.createExpense(form.value)
    toast.success('Egreso registrado correctamente')
    showModal.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}

/**
 * Prompts confirmation and removes an expense record from the ledger.
 * Enforces Administrator PIN authorization.
 * @param {Object} e - Expense record entity
 */
async function remove(e) {
  const confirmed = await askConfirm({
    title: '¿Eliminar Registro de Egreso?',
    message: '¿Estás seguro de que deseas eliminar permanentemente el registro contable:',
    itemName: `${e.concept} (${formatCurrency(e.amount)})`,
    itemType: 'expense',
    requiresAdmin: true,
    warningText: 'Operación financiera restringida: La eliminación de este gasto afectará los balances netos y el corte de caja del taller.'
  })

  if (!confirmed) return

  try {
    await store.deleteExpense(e.id)
    toast.success('Registro de egreso eliminado exitosamente.')
  } catch (err) {
    toast.error(err.message)
  }
}
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
  gap: 1rem;
  flex-wrap: wrap;
}
.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.cat-pill {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
  letter-spacing: 0.02em;
}
.cat-refacciones { background: #0b1e38; color: #60a5fa; border: 1px solid #2563eb; }
.cat-nomina      { background: #2e1a05; color: #fbbf24; border: 1px solid #d97706; }
.cat-herramientas{ background: #062419; color: #34d399; border: 1px solid #059669; }
.cat-renta       { background: #2f0911; color: #f87171; border: 1px solid #dc2626; }
.cat-servicios   { background: #131b2c; color: #cbd5e1; border: 1px solid #334155; }
.cat-otros       { background: #1a162b; color: #c084fc; border: 1px solid #7e22ce; }

.pay-badge {
  text-transform: capitalize;
  font-size: 0.8rem;
  color: var(--text-muted);
}

/* Custom Modal Header */
.expense-modal-header {
  display: flex;
  align-items: center;
  gap: 14px;
}
.expense-modal-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(244, 63, 94, 0.12));
  border: 1px solid rgba(244, 63, 94, 0.32);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #f87171;
  box-shadow: 0 4px 14px rgba(239, 68, 68, 0.2);
  flex-shrink: 0;
}
.expense-modal-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  line-height: 1.2;
}
.expense-modal-subtitle {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 3px;
  line-height: 1.35;
}

/* Modal Body & Panel Layout */
.expense-modal-body {
  padding: 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 78vh;
  overflow-y: auto;
}
.expense-panel {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 12px;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
}
.expense-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}
.expense-panel-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #e2e8f0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.expense-panel-title svg {
  color: #f87171;
}
.req-legend {
  font-size: 0.76rem;
  color: var(--text-muted);
}
.req-star {
  color: #f87171;
  font-weight: bold;
}
.form-label-hint {
  font-size: 0.76rem;
  color: var(--text-muted);
  font-weight: 400;
}

/* Form Inputs & Sizing */
.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.form-row-3 {
  display: grid;
  grid-template-columns: 1.15fr 1fr 1fr;
  gap: 14px;
}
.form-input-lg {
  height: 44px;
  font-size: 0.95rem;
  background: rgba(10, 15, 30, 0.8);
  border-color: rgba(148, 163, 184, 0.22);
}
.form-input-lg:focus {
  background: #0d162f;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
}
.input-has-error {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3) !important;
  background: rgba(239, 68, 68, 0.05) !important;
}
.form-field-error {
  font-size: 0.78rem;
  color: #fca5a5;
  font-weight: 600;
  margin-top: 2px;
}
.field-hint {
  font-size: 0.78rem;
  color: var(--text-muted);
  line-height: 1.4;
  margin-top: 4px;
}

/* Currency Input Styling */
.currency-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}
.currency-prefix {
  position: absolute;
  left: 12px;
  font-weight: 700;
  color: var(--text-muted);
  font-size: 0.95rem;
  pointer-events: none;
  z-index: 1;
}
.currency-field {
  padding-left: 28px !important;
  padding-right: 52px !important;
  font-weight: 700;
  font-family: var(--font-mono, monospace);
  font-size: 1rem;
}
.currency-suffix {
  position: absolute;
  right: 10px;
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--text-muted);
  background: rgba(148, 163, 184, 0.12);
  padding: 2px 6px;
  border-radius: 4px;
  pointer-events: none;
}

/* Cash Outflow Impact Banner */
.expense-impact-banner {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(244, 63, 94, 0.03));
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.impact-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.impact-title {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.84rem;
  font-weight: 700;
  color: #f87171;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.impact-subtitle {
  font-size: 0.82rem;
  color: var(--text-muted);
}
.impact-subtitle strong {
  color: #e2e8f0;
}
.impact-amount-box {
  display: flex;
  align-items: baseline;
  gap: 6px;
  text-align: right;
  flex-shrink: 0;
}
.impact-currency {
  font-size: 0.8rem;
  font-weight: 700;
  color: #fca5a5;
}
.impact-value {
  font-size: 1.45rem;
  font-weight: 800;
  color: #f87171;
  font-family: var(--font-mono, monospace);
  letter-spacing: -0.02em;
}

/* Modal Footer & Buttons */
.expense-modal-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  padding: 1.25rem 1.75rem;
  border-top: 1px solid rgba(148, 163, 184, 0.12);
  background: rgba(10, 15, 30, 0.4);
}
.btn-submit-expense {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  font-weight: 700;
}

.fiscal-tip {
  font-size: 0.74rem;
  color: #fbbf24;
  margin-top: 5px;
  line-height: 1.35;
  background: rgba(245, 158, 11, 0.08);
  border: 1px solid rgba(245, 158, 11, 0.25);
  border-radius: 6px;
  padding: 4px 8px;
}

@media (max-width: 680px) {
  .form-row-2,
  .form-row-3 {
    grid-template-columns: 1fr;
  }
  .expense-impact-banner {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  .impact-amount-box {
    align-self: flex-end;
  }
}
</style>
