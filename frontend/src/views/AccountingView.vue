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
        icon="&#128176;"
        label="Ingresos por Servicios"
        :value="'$' + store.totalRevenue.toFixed(2)"
        color="var(--success)"
      />
      <StatCard
        icon="&#128184;"
        label="Gastos Operativos"
        :value="'$' + store.totalExpenses.toFixed(2)"
        color="var(--danger)"
      />
      <StatCard
        icon="&#128200;"
        label="Utilidad Neta del Taller"
        :value="'$' + store.netProfit.toFixed(2)"
        color="var(--accent)"
      />
      <StatCard
        icon="&#128202;"
        label="Margen Operativo"
        :value="store.marginPct.toFixed(1) + '%'"
        color="var(--blue)"
      />
    </div>

    <!-- Card de egresos -->
    <div class="card" style="padding:0">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <div class="search-wrap">
            <span class="search-ic">&#128269;</span>
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
            <th>Concepto</th>
            <th>Categoría</th>
            <th>Método</th>
            <th>Folio/Ref</th>
            <th>Fecha</th>
            <th>Monto</th>
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
            <td class="font-semibold text-danger">-${{ parseFloat(e.amount).toFixed(2) }}</td>
            <td>
              <div class="td-actions">
                <button
                  class="btn-icon del"
                  @click="remove(e)"
                  :title="confirmDelete?.id === e.id ? 'Confirmar eliminación' : 'Eliminar'"
                  :style="confirmDelete?.id === e.id ? 'background:var(--danger-glow);opacity:1;color:var(--danger)' : ''"
                >
                  {{ confirmDelete?.id === e.id ? '?' : '&#128465;' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="empty-state" v-else>
        <div class="icon">&#128184;</div>
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
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import StatCard from '../components/ui/StatCard.vue'
import Modal from '../components/ui/Modal.vue'

const store = useStore()
const toast = useToast()

onMounted(async () => {
  await store.fetchOrders()
  await store.fetchExpenses()
})

const search = ref('')
const selectedCategory = ref('')
const showModal = ref(false)
const loading = ref(false)
const confirmDelete = ref(null)

const today = new Date().toISOString().split('T')[0]
const empty = () => ({
  concept: '',
  category: 'refacciones',
  amount: 0,
  payment_method: 'transferencia',
  reference: '',
  expense_date: today,
})
const form = ref(empty())

const filteredExpenses = computed(() => {
  const q = search.value.toLowerCase()
  return store.expenses.filter((e) => {
    const matchQ =
      (e.concept && e.concept.toLowerCase().includes(q)) ||
      (e.reference && e.reference.toLowerCase().includes(q))
    const matchCat = !selectedCategory.value || e.category === selectedCategory.value
    return matchQ && matchCat
  })
})

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

function openCreate() {
  form.value = empty()
  showModal.value = true
}

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

async function remove(e) {
  if (confirmDelete.value?.id === e.id) {
    try {
      await store.deleteExpense(e.id)
      toast.success('Egreso eliminado')
    } catch (err) {
      toast.error(err.message)
    }
    confirmDelete.value = null
  } else {
    confirmDelete.value = e
    setTimeout(() => {
      confirmDelete.value = null
    }, 3000)
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
