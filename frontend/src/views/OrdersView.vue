<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Ordenes de servicio</h2>
        <p class="page-subtitle">{{ store.orders.length }} ordenes en total</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ Nueva orden</button>
    </div>

    <div class="card" style="padding:0">
      <table class="data-table" v-if="store.orders.length">
        <thead>
          <tr><th>#</th><th>Cliente</th><th>Vehiculo</th><th>Estatus</th><th>Total</th><th>Cambiar estatus</th><th>Acc.</th></tr>
        </thead>
        <tbody>
          <tr v-for="o in store.orders" :key="o.id">
            <td class="text-muted">#{{ o.id }}</td>
            <td class="font-semibold">{{ o.customer_name || 'Sin nombre' }}</td>
            <td>{{ o.vehicle || '—' }}</td>
            <td><StatusBadge :status="o.status" /></td>
            <td class="font-semibold text-accent">${{ parseFloat(o.total).toFixed(2) }}</td>
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
                >&#128065;</button>
                <button
                  class="btn-icon del"
                  @click="removeOrder(o)"
                  :title="confirmDelete?.id === o.id ? 'Confirmar eliminacion' : 'Eliminar'"
                  :style="confirmDelete?.id === o.id ? 'background:var(--danger-glow);opacity:1;color:var(--danger)' : ''"
                >{{ confirmDelete?.id === o.id ? '?' : '&#128465;' }}</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="empty-state" v-else>
        <div class="icon">&#128203;</div>
        <p>No hay ordenes de servicio. Crea la primera.</p>
      </div>
    </div>

    <!-- Modal nueva orden -->
    <Modal v-model="showModal" title="Nueva orden de servicio" :wide="true">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nombre del cliente</label>
            <input v-model="form.customer_name" class="form-input" placeholder="Juan Perez" />
          </div>
          <div class="form-group">
            <label class="form-label">Vehiculo</label>
            <input v-model="form.vehicle" class="form-input" placeholder="Jetta 2018, Civic..." />
          </div>
        </div>

        <hr class="divider" />

        <!-- Items de la orden -->
        <div class="items-header">
          <label class="form-label">Productos del servicio</label>
          <button class="btn btn-ghost btn-sm" @click="addItem" type="button">+ Agregar</button>
        </div>

        <div v-for="(item, idx) in form.items" :key="idx" class="item-row">
          <select v-model.number="item.product_id" class="form-select">
            <option value="" disabled>Seleccionar producto...</option>
            <option v-for="p in store.products" :key="p.id" :value="p.id" :disabled="p.stock === 0">
              {{ p.name }} — ${{ p.price }} (stock: {{ p.stock }})
            </option>
          </select>
          <input v-model.number="item.qty" type="number" min="1" class="form-input" placeholder="Cant." />
          <button class="btn-icon del" @click="removeItem(idx)" :disabled="form.items.length === 1" type="button">x</button>
        </div>

        <div class="total-row">
          <span class="form-label">Total estimado</span>
          <span class="total-amount">${{ orderTotal.toFixed(2) }}</span>
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
              <td class="text-muted">${{ parseFloat(it.unit_price).toFixed(2) }}</td>
              <td class="text-accent font-semibold">${{ parseFloat(it.subtotal).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state" style="padding:1rem">
          <p>Esta orden no tiene refacciones registradas.</p>
        </div>

        <div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;margin-top:1.25rem;border-top:1px solid var(--border);padding-top:1rem">
          <span class="text-muted" style="font-size:0.95rem">Total de la orden:</span>
          <span class="text-accent" style="font-size:1.3rem;font-weight:800">${{ parseFloat(selectedOrder.total).toFixed(2) }}</span>
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

const store = useStore()
const toast = useToast()

onMounted(async () => {
  await store.fetchOrders()
  await store.fetchProducts()
})

const showModal = ref(false)
const showDetailModal = ref(false)
const selectedOrder = ref(null)
const creating = ref(false)
const confirmDelete = ref(null)

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