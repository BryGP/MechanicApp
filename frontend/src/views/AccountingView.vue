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

      <table class="data-table" v-if="filteredExpenses.length">
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
          <tr v-for="e in filteredExpenses" :key="e.id">
            <td class="font-semibold">{{ e.concept }}</td>
            <td>
              <span class="cat-pill" :class="'cat-' + e.category">
                {{ categoryLabel(e.category) }}
              </span>
            </td>
            <td><span class="pay-badge">{{ e.payment_method || 'efectivo' }}</span></td>
            <td><code>{{ e.reference || '—' }}</code></td>
            <td class="text-muted">{{ e.expense_date }}</td>
            <td class="font-semibold text-danger">-{{ formatCurrency(e.amount) }}</td>
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
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
        </div>
        <p>{{ search || selectedCategory ? 'No hay egresos con los filtros aplicados' : 'No hay gastos registrados aún. Agrega el primero.' }}</p>
      </div>
    </div>

    <!-- Modal Registrar Egreso -->
    <Modal v-model="showModal" title="Registrar nuevo egreso del taller">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Concepto del gasto *</label>
          <input
            v-model="form.concept"
            class="form-input"
            placeholder="Ej. Compra lote 20 filtros de aceite a Distribuidora Sur"
          />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Categoría *</label>
            <select v-model="form.category" class="form-select">
              <option value="refacciones">Refacciones e Insumos</option>
              <option value="herramientas">Herramientas y Equipo</option>
              <option value="nomina">Nómina de Mecánicos</option>
              <option value="renta">Renta del Taller</option>
              <option value="servicios">Servicios (Luz/Agua/Net)</option>
              <option value="otros">Otros Gastos</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Monto ($) *</label>
            <input
              v-model.number="form.amount"
              type="number"
              min="0.01"
              step="0.01"
              class="form-input"
              placeholder="0.00"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Método de pago</label>
            <select v-model="form.payment_method" class="form-select">
              <option value="efectivo">Efectivo</option>
              <option value="transferencia">Transferencia SPEI</option>
              <option value="tarjeta">Tarjeta Débito/Crédito</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Folio / Factura (Opcional)</label>
            <input v-model="form.reference" class="form-input" placeholder="FAC-8921 o Ticket" />
          </div>
          <div class="form-group">
            <label class="form-label">Fecha de egreso *</label>
            <input v-model="form.expense_date" type="date" class="form-input" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="save" :disabled="loading">
          {{ loading ? 'Guardando...' : 'Registrar egreso' }}
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

import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'
import StatCard from '../components/ui/StatCard.vue'
import Modal from '../components/ui/Modal.vue'
import SortableTh from '../components/ui/SortableTh.vue'
import { formatCurrency } from '../utils/format'

/** Global Pinia store instance */
const store = useStore()

/** Toast notification dispatcher */
const toast = useToast()

/** Centralized confirmation modal service */
const { askConfirm } = useConfirm()

/**
 * Initializes view data by fetching work orders and operational expenses.
 */
onMounted(async () => {
  await store.fetchOrders()
  await store.fetchExpenses()
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
  amount: 0,
  payment_method: 'transferencia',
  reference: '',
  expense_date: today,
})

/** Reactive form model for expense creation */
const form = ref(empty())

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
 * Opens the expense creation modal with fresh default values.
 */
function openCreate() {
  form.value = empty()
  showModal.value = true
}

/**
 * Validates and submits a new expense record to the backend ledger.
 */
async function save() {
  if (!form.value.concept.trim()) return toast.error('El concepto del gasto es requerido')
  if (!form.value.amount || form.value.amount <= 0) return toast.error('Ingresa un monto válido mayor a 0')
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
</style>
