<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Inventario</h2>
        <p class="page-subtitle">{{ store.products.length }} productos registrados</p>
      </div>
      <div class="toolbar">
        <div class="search-wrap">
          <span class="search-ic">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;display:block"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input v-model="search" class="form-input" placeholder="Buscar por nombre o SKU..." style="width:260px" />
        </div>
        <button class="btn btn-primary" @click="openCreate">+ Nuevo producto</button>
      </div>
    </div>

    <div class="card" style="padding:0">
      <table class="data-table" v-if="filtered.length">
        <thead>
          <tr>
            <th>Nombre</th><th>SKU</th><th>Precio</th><th>Stock</th><th>Min. Stock</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in filtered" :key="p.id">
            <td class="font-semibold">{{ p.name }}</td>
            <td><code style="background:var(--bg-hover);padding:2px 7px;border-radius:4px;font-size:0.8rem">{{ p.sku }}</code></td>
            <td class="text-accent font-semibold">${{ parseFloat(p.price).toFixed(2) }}</td>
            <td><StockBadge :stock="p.stock" :min-stock="p.min_stock" /></td>
            <td class="text-muted">{{ p.min_stock }}</td>
            <td>
              <div class="td-actions">
                <button class="btn-icon edit" @click="openEdit(p)" title="Editar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button
                  class="btn-icon del"
                  @click="remove(p)"
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
      <div class="empty-state" v-else>
        <div class="icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <p>{{ search ? 'Sin resultados para "' + search + '"' : 'No hay productos. Agrega el primero.' }}</p>
      </div>
    </div>

    <!-- Modal crear/editar -->
    <Modal v-model="showModal" :title="editingProduct ? 'Editar producto' : 'Nuevo producto'">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nombre del producto *</label>
          <input v-model="form.name" class="form-input" placeholder="Aceite 10W-30" />
        </div>
        <div class="form-group">
          <label class="form-label">SKU *</label>
          <input v-model="form.sku" class="form-input" placeholder="ACE-10W30" :disabled="!!editingProduct" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Precio ($) *</label>
            <input v-model.number="form.price" type="number" min="0" step="0.01" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Stock actual *</label>
            <input v-model.number="form.stock" type="number" min="0" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Stock minimo</label>
            <input v-model.number="form.min_stock" type="number" min="0" class="form-input" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="save" :disabled="loading">
          {{ loading ? 'Guardando...' : editingProduct ? 'Actualizar' : 'Crear producto' }}
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import Modal from '../components/ui/Modal.vue'
import StockBadge from '../components/ui/StockBadge.vue'

const store = useStore()
const toast = useToast()
onMounted(() => store.fetchProducts())

const search = ref('')
const showModal = ref(false)
const editingProduct = ref(null)
const loading = ref(false)
const confirmDelete = ref(null)

const empty = () => ({ name: '', sku: '', price: 0, stock: 0, min_stock: 0 })
const form = ref(empty())

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return store.products.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q))
})

function openCreate() {
  editingProduct.value = null
  form.value = empty()
  showModal.value = true
}
function openEdit(p) {
  editingProduct.value = p
  form.value = { name: p.name, sku: p.sku, price: parseFloat(p.price), stock: p.stock, min_stock: p.min_stock }
  showModal.value = true
}
async function save() {
  if (!form.value.name.trim() || !form.value.sku.trim()) return toast.error('Nombre y SKU son requeridos')
  loading.value = true
  try {
    if (editingProduct.value) {
      await store.updateProduct(editingProduct.value.id, form.value)
      toast.success('Producto actualizado correctamente')
    } else {
      await store.createProduct(form.value)
      toast.success('Producto creado exitosamente')
    }
    showModal.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}
async function remove(p) {
  if (confirmDelete.value?.id === p.id) {
    try {
      await store.deleteProduct(p.id)
      toast.success('Producto eliminado')
    } catch (e) { toast.error(e.message) }
    confirmDelete.value = null
  } else {
    confirmDelete.value = p
    setTimeout(() => { confirmDelete.value = null }, 3000)
  }
}
</script>