<template>
  <div>
    <!-- Encabezado Principal -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Inventario & Servicios</h2>
        <p class="page-subtitle">
          {{ physicalProducts.length }} refacciones físicas en almacén &bull; {{ serviceProducts.length }} servicios de taller
        </p>
      </div>
    </div>

    <!-- SECCIÓN 1 (SUPERIOR): INVENTARIO DE REFACCIONES FÍSICAS -->
    <div class="card" style="padding:0; margin-bottom: 1.25rem; overflow: hidden;">
      <div class="section-card-header">
        <div>
          <h3 class="section-card-title">Refacciones Físicas en Almacén</h3>
          <p class="section-card-subtitle">Control de stock de piezas, refacciones y fluidos con alertas de reposición</p>
        </div>
        <div class="toolbar">
          <div class="search-wrap">
            <span class="search-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input v-model="productSearch" class="form-input" placeholder="Buscar refacción o SKU..." style="width:230px" />
          </div>
          <select v-model="stockFilter" class="form-select" style="width: auto">
            <option value="">Todo el inventario</option>
            <option value="low">Stock bajo / crítico</option>
            <option value="ok">Niveles óptimos</option>
            <option value="zero">Sin existencias (0 pzas)</option>
          </select>
          <button class="btn btn-primary" @click="openCreateProduct">+ Nueva Refacción</button>
        </div>
      </div>

      <div class="table-scroll" v-if="filteredProducts.length">
        <table class="data-table">
          <thead>
            <tr>
              <SortableTh label="Refacción" field="name" :current-field="productSortField" :current-order="productSortOrder" @sort="handleProductSort" />
              <SortableTh label="SKU" field="sku" :current-field="productSortField" :current-order="productSortOrder" @sort="handleProductSort" />
              <SortableTh label="Precio" field="price" :current-field="productSortField" :current-order="productSortOrder" @sort="handleProductSort" />
              <SortableTh label="Stock" field="stock" :current-field="productSortField" :current-order="productSortOrder" @sort="handleProductSort" />
              <SortableTh label="Stock Mínimo" field="min_stock" :current-field="productSortField" :current-order="productSortOrder" @sort="handleProductSort" />
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in filteredProducts" :key="p.id">
              <td class="font-semibold">{{ p.name }}</td>
              <td><code style="background:var(--bg-hover);padding:2px 7px;border-radius:4px;font-size:0.8rem">{{ p.sku }}</code></td>
              <td class="text-accent font-semibold">{{ formatCurrency(p.price) }}</td>
              <td><StockBadge :stock="p.stock" :min-stock="p.min_stock" /></td>
              <td class="text-muted">{{ p.min_stock }}</td>
              <td>
                <div class="td-actions">
                  <button class="btn-icon edit" @click="openEditProduct(p)" title="Editar Refacción">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button
                    class="btn-icon del"
                    @click="removeProduct(p)"
                    :title="confirmDelete?.id === p.id ? 'Haz clic de nuevo para confirmar' : 'Eliminar'"
                    :style="confirmDelete?.id === p.id ? 'background:var(--danger-glow);opacity:1;color:var(--danger)' : ''"
                  >
                    <span v-if="confirmDelete?.id === p.id" style="font-size:0.75rem;font-weight:700">OK?</span>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <p>{{ productSearch || stockFilter ? 'Sin resultados para la búsqueda de refacciones.' : 'No hay refacciones registradas en almacén.' }}</p>
      </div>
    </div>

    <!-- SECCIÓN 2 (INFERIOR): CATÁLOGO DE SERVICIOS Y MANO DE OBRA -->
    <div class="card" style="padding:0; overflow: hidden;">
      <div class="section-card-header">
        <div>
          <h3 class="section-card-title">Servicios & Mano de Obra</h3>
          <p class="section-card-subtitle">Paquetes, diagnósticos y mano de obra del taller (sin control de stock)</p>
        </div>
        <div class="toolbar">
          <div class="search-wrap">
            <span class="search-ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input v-model="serviceSearch" class="form-input" placeholder="Buscar servicio o clave..." style="width:230px" />
          </div>
          <button class="btn btn-primary" @click="openCreateService">+ Nuevo Servicio</button>
        </div>
      </div>

      <div class="table-scroll" v-if="filteredServices.length">
        <table class="data-table">
          <thead>
            <tr>
              <SortableTh label="Servicio / Paquete" field="name" :current-field="serviceSortField" :current-order="serviceSortOrder" @sort="handleServiceSort" />
              <SortableTh label="Clave / Código" field="sku" :current-field="serviceSortField" :current-order="serviceSortOrder" @sort="handleServiceSort" />
              <SortableTh label="Tarifa al Cliente" field="price" :current-field="serviceSortField" :current-order="serviceSortOrder" @sort="handleServiceSort" />
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in filteredServices" :key="s.id">
              <td class="font-semibold">
                <div style="display:flex;align-items:center;gap:10px">
                  <span class="service-pill">SERVICIO</span>
                  <span>{{ s.name }}</span>
                </div>
              </td>
              <td><code style="background:var(--bg-hover);padding:2px 7px;border-radius:4px;font-size:0.8rem">{{ s.sku }}</code></td>
              <td class="text-accent font-semibold">{{ formatCurrency(s.price) }}</td>
              <td>
                <div class="td-actions">
                  <button class="btn-icon edit" @click="openEditService(s)" title="Editar Tarifa o Servicio">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button
                    class="btn-icon del"
                    @click="removeProduct(s)"
                    :title="confirmDelete?.id === s.id ? 'Haz clic de nuevo para confirmar' : 'Eliminar'"
                    :style="confirmDelete?.id === s.id ? 'background:var(--danger-glow);opacity:1;color:var(--danger)' : ''"
                  >
                    <span v-if="confirmDelete?.id === s.id" style="font-size:0.75rem;font-weight:700">OK?</span>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <p>{{ serviceSearch ? 'Sin resultados para la búsqueda de servicios.' : 'No hay servicios de taller registrados aún.' }}</p>
      </div>
    </div>

    <!-- MODAL 1: NUEVA / EDITAR REFACCIÓN FÍSICA -->
    <Modal v-model="showProductModal" :title="editingProduct ? 'Editar Refacción' : 'Nueva Refacción'">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nombre de la refacción *</label>
          <input v-model="formProduct.name" class="form-input" placeholder="Ej. Balatas Delanteras, Aceite 10W-30..." />
        </div>
        <div class="form-group">
          <label class="form-label">SKU / Código *</label>
          <input v-model="formProduct.sku" class="form-input" placeholder="Ej. BAL-DEL-01" :disabled="!!editingProduct" />
        </div>
        <div class="form-row-3">
          <div class="form-group">
            <label class="form-label">Precio ($) *</label>
            <input v-model.number="formProduct.price" type="number" min="0" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Stock actual *</label>
            <input v-model.number="formProduct.stock" type="number" min="0" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Stock mínimo</label>
            <input v-model.number="formProduct.min_stock" type="number" min="0" class="form-input" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showProductModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="saveProduct" :disabled="loading">
          {{ loading ? 'Guardando...' : editingProduct ? 'Actualizar Refacción' : 'Crear Refacción' }}
        </button>
      </div>
    </Modal>

    <!-- MODAL 2: NUEVO / EDITAR SERVICIO DE TALLER -->
    <Modal v-model="showServiceModal" :title="editingService ? 'Editar Servicio' : 'Nuevo Servicio de Taller'">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nombre del servicio o mano de obra *</label>
          <input v-model="formService.name" class="form-input" placeholder="Ej. Alineación y Balanceo, Diagnóstico por Escáner..." />
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Clave / Código *</label>
            <input v-model="formService.sku" class="form-input" placeholder="Ej. SERV-ALIN-01" :disabled="!!editingService" />
          </div>
          <div class="form-group">
            <label class="form-label">Tarifa al cliente ($) *</label>
            <input v-model.number="formService.price" type="number" min="0" step="0.01" class="form-input" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showServiceModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="saveService" :disabled="loading">
          {{ loading ? 'Guardando...' : editingService ? 'Actualizar Servicio' : 'Crear Servicio' }}
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import { formatCurrency } from '../utils/format'
import Modal from '../components/ui/Modal.vue'
import StockBadge from '../components/ui/StockBadge.vue'
import SortableTh from '../components/ui/SortableTh.vue'

const store = useStore()
const toast = useToast()
onMounted(() => store.fetchProducts())

// Estado de Refacciones
const productSearch = ref('')
const stockFilter = ref('')
const productSortField = ref('name')
const productSortOrder = ref('asc')
const showProductModal = ref(false)
const editingProduct = ref(null)

// Estado de Servicios
const serviceSearch = ref('')
const serviceSortField = ref('name')
const serviceSortOrder = ref('asc')
const showServiceModal = ref(false)
const editingService = ref(null)

const loading = ref(false)
const confirmDelete = ref(null)

// Formularios
const emptyProduct = () => ({ name: '', sku: '', price: 0, stock: 0, min_stock: 0, is_service: false })
const emptyService = () => ({ name: '', sku: '', price: 0, stock: 0, min_stock: 0, is_service: true })

const formProduct = ref(emptyProduct())
const formService = ref(emptyService())

// Separación de colecciones
const physicalProducts = computed(() => store.products.filter(p => !p.is_service))
const serviceProducts = computed(() => store.products.filter(p => !!p.is_service))

// Ordenamiento de Refacciones
function handleProductSort(field) {
  if (productSortField.value === field) {
    productSortOrder.value = productSortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    productSortField.value = field
    productSortOrder.value = field === 'price' || field === 'stock' ? 'desc' : 'asc'
  }
}

const filteredProducts = computed(() => {
  const q = productSearch.value.toLowerCase().trim()
  let list = physicalProducts.value.filter(p => {
    const matchSearch = !q || p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
    let matchStock = true
    if (stockFilter.value === 'low') matchStock = p.stock <= p.min_stock
    else if (stockFilter.value === 'ok') matchStock = p.stock > p.min_stock
    else if (stockFilter.value === 'zero') matchStock = p.stock === 0
    return matchSearch && matchStock
  })

  return list.sort((a, b) => {
    let valA = a[productSortField.value]
    let valB = b[productSortField.value]
    if (productSortField.value === 'price' || productSortField.value === 'stock' || productSortField.value === 'min_stock') {
      valA = parseFloat(valA || 0)
      valB = parseFloat(valB || 0)
      return productSortOrder.value === 'asc' ? valA - valB : valB - valA
    } else {
      valA = String(valA || '')
      valB = String(valB || '')
      const cmp = valA.localeCompare(valB, 'es', { numeric: true, sensitivity: 'base' })
      return productSortOrder.value === 'asc' ? cmp : -cmp
    }
  })
})

// Ordenamiento de Servicios
function handleServiceSort(field) {
  if (serviceSortField.value === field) {
    serviceSortOrder.value = serviceSortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    serviceSortField.value = field
    serviceSortOrder.value = field === 'price' ? 'desc' : 'asc'
  }
}

const filteredServices = computed(() => {
  const q = serviceSearch.value.toLowerCase().trim()
  let list = serviceProducts.value.filter(s => {
    return !q || s.name.toLowerCase().includes(q) || s.sku.toLowerCase().includes(q)
  })

  return list.sort((a, b) => {
    let valA = a[serviceSortField.value]
    let valB = b[serviceSortField.value]
    if (serviceSortField.value === 'price') {
      valA = parseFloat(valA || 0)
      valB = parseFloat(valB || 0)
      return serviceSortOrder.value === 'asc' ? valA - valB : valB - valA
    } else {
      valA = String(valA || '')
      valB = String(valB || '')
      const cmp = valA.localeCompare(valB, 'es', { numeric: true, sensitivity: 'base' })
      return serviceSortOrder.value === 'asc' ? cmp : -cmp
    }
  })
})

// Acciones para Refacciones
function openCreateProduct() {
  editingProduct.value = null
  formProduct.value = emptyProduct()
  showProductModal.value = true
}

function openEditProduct(p) {
  editingProduct.value = p
  formProduct.value = {
    name: p.name,
    sku: p.sku,
    price: parseFloat(p.price),
    stock: p.stock,
    min_stock: p.min_stock,
    is_service: false
  }
  showProductModal.value = true
}

async function saveProduct() {
  if (!formProduct.value.name.trim() || !formProduct.value.sku.trim()) {
    return toast.error('Nombre y SKU son requeridos')
  }
  loading.value = true
  try {
    if (editingProduct.value) {
      await store.updateProduct(editingProduct.value.id, formProduct.value)
      toast.success('Refacción actualizada correctamente')
    } else {
      await store.createProduct(formProduct.value)
      toast.success('Refacción creada exitosamente')
    }
    showProductModal.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}

// Acciones para Servicios
function openCreateService() {
  editingService.value = null
  formService.value = emptyService()
  showServiceModal.value = true
}

function openEditService(s) {
  editingService.value = s
  formService.value = {
    name: s.name,
    sku: s.sku,
    price: parseFloat(s.price),
    stock: 0,
    min_stock: 0,
    is_service: true
  }
  showServiceModal.value = true
}

async function saveService() {
  if (!formService.value.name.trim() || !formService.value.sku.trim()) {
    return toast.error('Nombre y Clave del servicio son requeridos')
  }
  loading.value = true
  try {
    if (editingService.value) {
      await store.updateProduct(editingService.value.id, formService.value)
      toast.success('Servicio actualizado correctamente')
    } else {
      await store.createProduct(formService.value)
      toast.success('Servicio creado exitosamente')
    }
    showServiceModal.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}

// Eliminar cualquiera
async function removeProduct(item) {
  if (confirmDelete.value?.id === item.id) {
    try {
      await store.deleteProduct(item.id)
      toast.success(item.is_service ? 'Servicio eliminado' : 'Refacción eliminada')
    } catch (e) {
      toast.error(e.message)
    }
    confirmDelete.value = null
  } else {
    confirmDelete.value = item
    setTimeout(() => { confirmDelete.value = null }, 3000)
  }
}
</script>

<style scoped>
.section-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
  gap: 1rem;
  position: relative;
  z-index: 15;
  background: var(--bg-card);
}
.section-card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
}
.section-card-subtitle {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 3px;
}
.service-pill {
  padding: 2px 8px;
  background: rgba(14, 165, 233, 0.12);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
  border-radius: 4px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}
</style>