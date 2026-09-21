<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Órdenes de Servicio</h2>
        <p class="page-subtitle">{{ store.orders.length }} órdenes registradas</p>
      </div>
      <div class="toolbar">
        <div class="search-wrap">
          <span class="search-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input v-model="search" class="form-input" placeholder="Buscar cliente o auto..." style="width:230px" />
        </div>
        <select v-model="statusFilter" class="form-select" style="width: auto">
          <option value="">Todos los estatus</option>
          <option value="open">Abiertas</option>
          <option value="in_progress">En proceso</option>
          <option value="done">Terminadas</option>
          <option value="delivered">Entregadas</option>
        </select>
        <button class="btn btn-primary" @click="openCreate">+ Nueva Orden</button>
      </div>
    </div>

    <div class="card" style="padding:0">
      <table class="data-table" v-if="filteredOrders.length">
        <thead>
          <tr>
            <SortableTh label="#" field="id" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
            <SortableTh label="Cliente" field="customer_name" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
            <SortableTh label="Vehículo" field="vehicle" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
            <SortableTh label="Estatus" field="status" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
            <SortableTh label="Total" field="total" :current-field="sortField" :current-order="sortOrder" @sort="handleSort" />
            <th>Cambiar estatus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="o in filteredOrders" :key="o.id">
            <td class="text-muted">#{{ o.id }}</td>
            <td class="font-semibold">{{ o.customer_name || 'Sin nombre' }}</td>
            <td>{{ o.vehicle || '—' }}</td>
            <td><StatusBadge :status="o.status" /></td>
            <td class="font-semibold text-accent">{{ formatCurrency(o.total) }}</td>
            <td>
              <select class="form-select" style="padding:5px 8px;font-size:0.8rem;width:auto" :value="o.status" @change="changeStatus(o, $event.target.value)">
                <option value="open">Abierta</option>
                <option value="in_progress">En proceso</option>
                <option value="done">Terminada</option>
                <option value="delivered">Entregada</option>
              </select>
            </td>
            <td>
              <div class="td-actions">
                <button
                  class="btn-icon"
                  @click="openDetail(o)"
                  title="Ver refacciones y detalles del servicio"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <button
                  class="btn-icon del"
                  @click="removeOrder(o)"
                  :title="confirmDelete?.id === o.id ? 'Confirmar eliminación' : 'Eliminar'"
                  :style="confirmDelete?.id === o.id ? 'background:var(--danger-glow);opacity:1;color:var(--danger)' : ''"
                >
                  <span v-if="confirmDelete?.id === o.id" style="font-size:0.75rem;font-weight:700">OK?</span>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
        </div>
        <p>{{ search || statusFilter ? 'No hay órdenes que coincidan con los filtros aplicados.' : 'No hay órdenes de servicio. Crea la primera.' }}</p>
      </div>
    </div>

    <!-- Modal nueva orden -->
    <Modal v-model="showModal" title="Nueva Orden de Servicio" :wide="true">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nombre del cliente</label>
            <input v-model="form.customer_name" class="form-input" placeholder="Ej. Juan Pérez" />
          </div>
          <div class="form-group">
            <label class="form-label">Vehículo</label>
            <input v-model="form.vehicle" class="form-input" placeholder="Ej. Jetta 2018, Civic..." />
          </div>
        </div>

        <hr class="divider" />

        <!-- Items de la orden -->
        <div class="items-header">
          <label class="form-label">Refacciones y partes del servicio</label>
          <button class="btn btn-ghost btn-sm" @click="addItem" type="button">+ Agregar</button>
        </div>

        <div v-for="(item, idx) in form.items" :key="idx" class="item-row">
          <select v-model.number="item.product_id" class="form-select">
            <option value="" disabled>Seleccionar producto o servicio...</option>
            <option v-for="p in store.products" :key="p.id" :value="p.id" :disabled="!p.is_service && p.stock === 0">
              {{ p.is_service ? '🔧 [Servicio] ' : '📦 ' }}{{ p.name }} — {{ formatCurrency(p.price) }}{{ p.is_service ? '' : ' (stock: ' + p.stock + ')' }}
            </option>
          </select>
          <input v-model.number="item.qty" type="number" min="1" class="form-input" placeholder="Cant." />
          <button class="btn-icon del" @click="removeItem(idx)" :disabled="form.items.length === 1" type="button">x</button>
        </div>

        <div class="total-row">
          <span class="form-label">Total estimado</span>
          <span class="total-amount">{{ formatCurrency(orderTotal) }}</span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="submitOrder" :disabled="creating">
          {{ creating ? 'Creando orden...' : 'Crear orden' }}
        </button>
      </div>
    </Modal>

    <!-- Modal detalle de orden -->
    <Modal v-model="showDetailModal" :title="'Detalle de orden #' + (selectedOrder?.id || '')" :wide="true">
      <div class="modal-body" v-if="selectedOrder">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;background:var(--bg-hover);padding:1rem;border-radius:var(--radius-sm)">
          <div>
            <div style="font-size:1.1rem;font-weight:700;color:var(--text)">{{ selectedOrder.customer_name || 'Sin nombre de cliente' }}</div>
            <div class="text-muted" style="font-size:0.875rem;margin-top:4px">{{ selectedOrder.vehicle || 'Sin descripción de vehículo' }}</div>
          </div>
          <div>
            <StatusBadge :status="selectedOrder.status" />
          </div>
        </div>

        <h4 style="font-size:0.95rem;font-weight:700;margin-bottom:0.75rem">Refacciones y componentes utilizados</h4>
        <table class="data-table" v-if="selectedOrder.items?.length">
          <thead>
            <tr>
              <th>Refacción</th>
              <th>SKU</th>
              <th>Cant.</th>
              <th>P. Unitario</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="it in selectedOrder.items" :key="it.id">
              <td class="font-semibold">{{ it.product?.name || ('Producto #' + it.product_id) }}</td>
              <td><code style="background:var(--bg-hover);padding:2px 7px;border-radius:4px;font-size:0.8rem">{{ it.product?.sku || '—' }}</code></td>
              <td>{{ it.quantity || it.qty }}</td>
              <td class="text-muted">{{ formatCurrency(it.unit_price) }}</td>
              <td class="text-accent font-semibold">{{ formatCurrency(it.subtotal) }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state" style="padding:1rem">
          <p>Esta orden no tiene refacciones registradas.</p>
        </div>

        <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-top:1.25rem;border-top:1px solid var(--border);padding-top:1rem">
          <span class="text-muted" style="font-size:0.95rem">Total de la orden:</span>
          <span class="text-accent" style="font-size:1.3rem;font-weight:800">{{ formatCurrency(selectedOrder.total) }}</span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showDetailModal = false">Cerrar</button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import SortableTh from '../components/ui/SortableTh.vue'
import { formatCurrency } from '../utils/format'

const store = useStore()
const toast = useToast()

onMounted(async () => {
  await store.fetchOrders()
  await store.fetchProducts()
})

const search = ref('')
const statusFilter = ref('')
const sortField = ref('id')
const sortOrder = ref('desc')
const showModal = ref(false)
const showDetailModal = ref(false)
const selectedOrder = ref(null)
const creating = ref(false)
const confirmDelete = ref(null)

function handleSort(field) {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = field === 'id' || field === 'total' ? 'desc' : 'asc'
  }
}

const statusWeight = { open: 1, in_progress: 2, done: 3, delivered: 4 }

const filteredOrders = computed(() => {
  const q = search.value.toLowerCase().trim()
  let list = store.orders.filter(o => {
    const matchSearch =
      !q ||
      (o.customer_name && o.customer_name.toLowerCase().includes(q)) ||
      (o.vehicle && o.vehicle.toLowerCase().includes(q)) ||
      String(o.id).includes(q)
    const matchStatus = !statusFilter.value || o.status === statusFilter.value
    return matchSearch && matchStatus
  })

  return list.sort((a, b) => {
    let valA = a[sortField.value]
    let valB = b[sortField.value]
    if (sortField.value === 'id') {
      return sortOrder.value === 'asc' ? a.id - b.id : b.id - a.id
    } else if (sortField.value === 'total') {
      valA = parseFloat(valA || 0)
      valB = parseFloat(valB || 0)
      return sortOrder.value === 'asc' ? valA - valB : valB - valA
    } else if (sortField.value === 'status') {
      valA = statusWeight[valA] || 0
      valB = statusWeight[valB] || 0
      return sortOrder.value === 'asc' ? valA - valB : valB - valA
    } else {
      valA = String(valA || '')
      valB = String(valB || '')
      const cmp = valA.localeCompare(valB, 'es', { numeric: true, sensitivity: 'base' })
      return sortOrder.value === 'asc' ? cmp : -cmp
    }
  })
})

function openDetail(o) {
  selectedOrder.value = o
  showDetailModal.value = true
}

const emptyForm = () => ({ customer_name: '', vehicle: '', items: [{ product_id: '', qty: 1 }] })
const form = ref(emptyForm())

const orderTotal = computed(() =>
  form.value.items.reduce((sum, item) => {
    const p = store.products.find(x => x.id === item.product_id)
    return sum + (p ? parseFloat(p.price) * (item.qty || 0) : 0)
  }, 0)
)

function openCreate() { form.value = emptyForm(); showModal.value = true }
function addItem() { form.value.items.push({ product_id: '', qty: 1 }) }
function removeItem(idx) { form.value.items.splice(idx, 1) }

async function submitOrder() {
  const valid = form.value.items.filter(i => i.product_id && i.qty > 0)
  if (!valid.length) return toast.error('Agrega al menos un producto a la orden')
  creating.value = true
  try {
    await store.createOrder({ ...form.value, items: valid })
    toast.success('Orden creada exitosamente')
    showModal.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    creating.value = false
  }
}

async function changeStatus(order, status) {
  try {
    await store.updateOrderStatus(order.id, status)
    toast.success('Estatus actualizado')
  } catch (e) { toast.error(e.message) }
}

async function removeOrder(order) {
  if (confirmDelete.value?.id === order.id) {
    try {
      await store.deleteOrder(order.id)
      toast.success('Orden eliminada')
    } catch (e) { toast.error(e.message) }
    confirmDelete.value = null
  } else {
    confirmDelete.value = order
    setTimeout(() => { confirmDelete.value = null }, 3000)
  }
}
</script>