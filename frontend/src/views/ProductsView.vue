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
                    title="Eliminar Refacción"
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
                    title="Eliminar Servicio"
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
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <p>{{ serviceSearch ? 'Sin resultados para la búsqueda de servicios.' : 'No hay servicios de taller registrados aún.' }}</p>
      </div>
    </div>

    <!-- MODAL 1: NUEVA / EDITAR REFACCIÓN FÍSICA -->
    <Modal v-model="showProductModal" :wide="true">
      <template #header>
        <div class="product-modal-header">
          <div class="product-modal-icon part-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
          </div>
          <div>
            <div class="product-modal-title">
              {{ editingProduct ? 'Editar Refacción en Almacén' : 'Nueva Refacción de Almacén' }}
            </div>
            <div class="product-modal-subtitle">
              {{ editingProduct ? 'Modifica los precios, existencias o catálogo de la pieza' : 'Registra una nueva pieza física para el control de inventario y refacciones' }}
            </div>
          </div>
        </div>
      </template>

      <div class="modal-body product-modal-body">
        <!-- Panel 1: Identificación y Catálogo -->
        <div class="product-panel">
          <div class="product-panel-header">
            <span class="product-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
              Identificación y Catálogo
            </span>
            <span class="req-legend"><span class="req-star">*</span> Campos obligatorios</span>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                Nombre de la refacción <span class="req-star">*</span>
              </label>
              <input
                v-model="formProduct.name"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedProduct && !formProduct.name.trim() }"
                placeholder="Ej. Balatas Cerámicas Brembo Delanteras, Aceite 5W-30..."
              />
              <span v-if="submittedProduct && !formProduct.name.trim()" class="form-field-error">
                El nombre de la refacción es obligatorio
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">
                SKU / Código de Almacén <span class="req-star">*</span>
              </label>
              <input
                v-model="formProduct.sku"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedProduct && !formProduct.sku.trim() }"
                placeholder="Ej. BAL-BREM-01"
                :disabled="!!editingProduct"
              />
              <span v-if="submittedProduct && !formProduct.sku.trim()" class="form-field-error">
                El código SKU es obligatorio
              </span>
              <span class="field-hint" v-else>
                {{ editingProduct ? 'El SKU no puede ser modificado para mantener la trazabilidad.' : 'Código único e irrepetible para control de existencias.' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Panel 2: Precios y Stock -->
        <div class="product-panel">
          <div class="product-panel-header">
            <span class="product-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              Precio al Público y Control de Stock
            </span>
          </div>

          <div class="form-row-3">
            <div class="form-group">
              <label class="form-label">
                Precio de Venta ($ MXN) <span class="req-star">*</span>
              </label>
              <input
                v-model.number="formProduct.price"
                type="number"
                min="0.01"
                step="0.01"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedProduct && (formProduct.price === null || formProduct.price === '' || formProduct.price <= 0) }"
                placeholder="0.00"
              />
              <span v-if="submittedProduct && (formProduct.price === null || formProduct.price === '' || formProduct.price <= 0)" class="form-field-error">
                El precio debe ser mayor a $0.00
              </span>
              <span class="field-hint" v-else>Importe unitario cotizado al cliente (obligatorio > $0).</span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Stock Físico Actual <span class="req-star">*</span>
              </label>
              <input
                v-model.number="formProduct.stock"
                type="number"
                min="0"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedProduct && (formProduct.stock === null || formProduct.stock === '' || formProduct.stock < 0) }"
                placeholder="0"
              />
              <span v-if="submittedProduct && (formProduct.stock === null || formProduct.stock === '' || formProduct.stock < 0)" class="form-field-error">
                Stock inválido
              </span>
              <span class="field-hint" v-else>Piezas disponibles hoy en anaquel.</span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Stock Mínimo (Alerta)
              </label>
              <input
                v-model.number="formProduct.min_stock"
                type="number"
                min="0"
                class="form-input form-input-lg"
                placeholder="0"
              />
              <span class="field-hint">Alerta de resurtido al llegar a este umbral.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer product-modal-footer">
        <button class="btn btn-ghost" @click="showProductModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary btn-submit-product" @click="saveProduct" :disabled="loading" type="button">
          <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>{{ loading ? 'Guardando...' : editingProduct ? 'Actualizar Refacción' : 'Registrar Refacción' }}</span>
        </button>
      </div>
    </Modal>

    <!-- MODAL 2: NUEVO / EDITAR SERVICIO DE TALLER -->
    <Modal v-model="showServiceModal" :wide="true">
      <template #header>
        <div class="product-modal-header">
          <div class="product-modal-icon service-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <div>
            <div class="product-modal-title">
              {{ editingService ? 'Editar Servicio de Taller' : 'Nuevo Servicio de Mano de Obra' }}
            </div>
            <div class="product-modal-subtitle">
              {{ editingService ? 'Actualiza la tarifa o clave del trabajo mecánico' : 'Registra mano de obra, afinaciones, revisiones o diagnósticos' }}
            </div>
          </div>
        </div>
      </template>

      <div class="modal-body product-modal-body">
        <div class="product-panel">
          <div class="product-panel-header">
            <span class="product-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              Datos del Servicio y Mano de Obra
            </span>
            <span class="req-legend"><span class="req-star">*</span> Campos obligatorios</span>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                Nombre del servicio mecánico <span class="req-star">*</span>
              </label>
              <input
                v-model="formService.name"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedService && !formService.name.trim() }"
                placeholder="Ej. Afinación Mayor y Cambio de Bujías, Escaneo OBD-II..."
              />
              <span v-if="submittedService && !formService.name.trim()" class="form-field-error">
                El nombre del servicio es obligatorio
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Clave / Código del Servicio <span class="req-star">*</span>
              </label>
              <input
                v-model="formService.sku"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedService && !formService.sku.trim() }"
                placeholder="Ej. SERV-AFIN-MAYOR"
                :disabled="!!editingService"
              />
              <span v-if="submittedService && !formService.sku.trim()" class="form-field-error">
                La clave del servicio es obligatoria
              </span>
              <span class="field-hint" v-else>
                {{ editingService ? 'La clave no se puede modificar para conservar el histórico.' : 'Identificador alfanumérico único para este servicio.' }}
              </span>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              Tarifa al Cliente ($ MXN) <span class="req-star">*</span>
            </label>
            <div style="max-width: 320px;">
              <input
                v-model.number="formService.price"
                type="number"
                min="0.01"
                step="0.01"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submittedService && (formService.price === null || formService.price === '' || formService.price <= 0) }"
                placeholder="0.00"
              />
            </div>
            <span v-if="submittedService && (formService.price === null || formService.price === '' || formService.price <= 0)" class="form-field-error">
              La tarifa debe ser un número válido mayor a $0.00
            </span>
            <span class="field-hint" v-else>
              Costo de mano de obra cobrado al cliente (obligatorio > $0). Los servicios no descuentan inventario físico.
            </span>
          </div>
        </div>
      </div>

      <div class="modal-footer product-modal-footer">
        <button class="btn btn-ghost" @click="showServiceModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary btn-submit-service" @click="saveService" :disabled="loading" type="button">
          <svg v-if="!loading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <span>{{ loading ? 'Guardando...' : editingService ? 'Actualizar Servicio' : 'Registrar Servicio' }}</span>
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
/**
 * @fileoverview Inventory Parts & Labor Services Catalog View
 * @module views/ProductsView
 * @description Manages the auto shop's physical spare parts stock and labor service catalog:
 * - Physical parts: stock count, minimum safety threshold alerts, purchase pricing.
 * - Labor services: flat rate labor fees, zero-inventory management.
 * - Search, sort, creation, editing modals, duplicate validation, and deletion dialogs.
 */

import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'
import { formatCurrency } from '../utils/format'
import Modal from '../components/ui/Modal.vue'
import StockBadge from '../components/ui/StockBadge.vue'
import SortableTh from '../components/ui/SortableTh.vue'

/** Global Pinia state store */
const store = useStore()

/** Toast notification dispatcher */
const toast = useToast()

/** Centralized confirmation modal service */
const { askConfirm } = useConfirm()

/** Loads catalog products on initial view mounting */
onMounted(() => store.fetchProducts())

// -----------------------------------------------------------------------------
// Physical Products (Spare Parts) State
// -----------------------------------------------------------------------------
const productSearch = ref('')
const stockFilter = ref('')
const productSortField = ref('name')
const productSortOrder = ref('asc')
const showProductModal = ref(false)
const editingProduct = ref(null)

// -----------------------------------------------------------------------------
// Labor Services Catalog State
// -----------------------------------------------------------------------------
const serviceSearch = ref('')
const serviceSortField = ref('name')
const serviceSortOrder = ref('asc')
const showServiceModal = ref(false)
const editingService = ref(null)

const loading = ref(false)
const submittedProduct = ref(false)
const submittedService = ref(false)

/**
 * Factory for creating an empty physical product payload.
 * @returns {Object} Empty product object
 */
const emptyProduct = () => ({
  name: '',
  sku: '',
  price: null,
  stock: 0,
  min_stock: 0,
  is_service: false
})

/**
 * Factory for creating an empty labor service payload.
 * @returns {Object} Empty service object
 */
const emptyService = () => ({
  name: '',
  sku: '',
  price: null,
  stock: 0,
  min_stock: 0,
  is_service: true
})

const formProduct = ref(emptyProduct())
const formService = ref(emptyService())

/** Computed list of physical inventory spare parts */
const physicalProducts = computed(() => store.products.filter(p => !p.is_service))

/** Computed list of labor services */
const serviceProducts = computed(() => store.products.filter(p => !!p.is_service))

// -----------------------------------------------------------------------------
// Sort Handlers for Physical Spare Parts
// -----------------------------------------------------------------------------

/**
 * Handles sorting column toggle for physical inventory items.
 * @param {string} field - Property name to sort by
 */
function handleProductSort(field) {
  if (productSortField.value === field) {
    productSortOrder.value = productSortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    productSortField.value = field
    productSortOrder.value = field === 'price' || field === 'stock' ? 'desc' : 'asc'
  }
}

/**
 * Filtered and sorted collection of physical spare parts.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
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

// -----------------------------------------------------------------------------
// Sort Handlers for Labor Services
// -----------------------------------------------------------------------------

/**
 * Handles sorting column toggle for labor services.
 * @param {string} field - Property name to sort by
 */
function handleServiceSort(field) {
  if (serviceSortField.value === field) {
    serviceSortOrder.value = serviceSortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    serviceSortField.value = field
    serviceSortOrder.value = field === 'price' ? 'desc' : 'asc'
  }
}

/**
 * Filtered and sorted collection of labor services.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
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

// -----------------------------------------------------------------------------
// Physical Products Modal & CRUD Actions
// -----------------------------------------------------------------------------

/**
 * Opens the creation modal for registering a physical spare part.
 */
function openCreateProduct() {
  editingProduct.value = null
  formProduct.value = emptyProduct()
  submittedProduct.value = false
  showProductModal.value = true
}

/**
 * Opens the edit modal with the selected physical product's data.
 * @param {Object} p - Product item to edit
 */
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
  submittedProduct.value = false
  showProductModal.value = true
}

/**
 * Validates and saves a physical product (new or update).
 */
async function saveProduct() {
  submittedProduct.value = true
  const name = formProduct.value.name?.trim()
  const sku = formProduct.value.sku?.trim()
  const price = formProduct.value.price
  const stock = formProduct.value.stock

  if (!name) {
    return toast.error('El nombre de la refacción es obligatorio.')
  }
  if (!sku) {
    return toast.error('El código o SKU de la refacción es obligatorio.')
  }
  if (price === null || price === undefined || price === '' || isNaN(price) || Number(price) <= 0) {
    return toast.error('El precio de venta debe ser obligatorio y mayor a $0.00.')
  }
  if (stock === null || stock === undefined || stock === '' || isNaN(stock) || Number(stock) < 0) {
    return toast.error('El stock físico debe ser un número entero mayor o igual a 0.')
  }

  // Client-side duplicate prevention
  const editId = editingProduct.value?.id
  const skuDupe = store.products.find(p => p.sku?.trim().toLowerCase() === sku.toLowerCase() && p.id !== editId)
  if (skuDupe) {
    return toast.error(`¡El SKU "${sku}" ya existe en el sistema! No se permiten duplicados.`)
  }

  const nameDupe = store.products.find(p => p.name?.trim().toLowerCase() === name.toLowerCase() && p.id !== editId)
  if (nameDupe) {
    return toast.error(`¡Ya existe una refacción registrada con el nombre "${name}"!`)
  }

  loading.value = true
  try {
    const payload = {
      ...formProduct.value,
      name,
      sku,
      price: Number(price),
      stock: Number(stock)
    }
    if (editingProduct.value) {
      await store.updateProduct(editingProduct.value.id, payload)
      toast.success('¡Refacción actualizada correctamente!')
    } else {
      await store.createProduct(payload)
      toast.success('¡Refacción agregada exitosamente al almacén!')
    }
    showProductModal.value = false
    submittedProduct.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}

// -----------------------------------------------------------------------------
// Labor Services Modal & CRUD Actions
// -----------------------------------------------------------------------------

/**
 * Opens the creation modal for registering a new labor service.
 */
function openCreateService() {
  editingService.value = null
  formService.value = emptyService()
  submittedService.value = false
  showServiceModal.value = true
}

/**
 * Opens the edit modal with the selected labor service's data.
 * @param {Object} s - Service item to edit
 */
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
  submittedService.value = false
  showServiceModal.value = true
}

/**
 * Validates and saves a labor service catalog item.
 */
async function saveService() {
  submittedService.value = true
  const name = formService.value.name?.trim()
  const sku = formService.value.sku?.trim()
  const price = formService.value.price

  if (!name) {
    return toast.error('El nombre del servicio de taller es obligatorio.')
  }
  if (!sku) {
    return toast.error('La clave o código del servicio es obligatoria.')
  }
  if (price === null || price === undefined || price === '' || isNaN(price) || Number(price) <= 0) {
    return toast.error('La tarifa al cliente debe ser obligatoria y mayor a $0.00.')
  }

  // Client-side duplicate prevention
  const editId = editingService.value?.id
  const skuDupe = store.products.find(p => p.sku?.trim().toLowerCase() === sku.toLowerCase() && p.id !== editId)
  if (skuDupe) {
    return toast.error(`¡La clave/código "${sku}" ya está en uso por otro servicio o refacción!`)
  }

  const nameDupe = store.products.find(p => p.name?.trim().toLowerCase() === name.toLowerCase() && p.id !== editId)
  if (nameDupe) {
    return toast.error(`¡Ya existe un servicio registrado con el nombre "${name}"!`)
  }

  loading.value = true
  try {
    const payload = {
      ...formService.value,
      name,
      sku,
      price: Number(price),
      stock: 0,
      min_stock: 0
    }
    if (editingService.value) {
      await store.updateProduct(editingService.value.id, payload)
      toast.success('¡Servicio actualizado correctamente!')
    } else {
      await store.createProduct(payload)
      toast.success('¡Nuevo servicio agregado exitosamente al catálogo!')
    }
    showServiceModal.value = false
    submittedService.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    loading.value = false
  }
}

// -----------------------------------------------------------------------------
// Centralized Deletion Workflow
// -----------------------------------------------------------------------------

/**
 * Prompts confirmation and removes a catalog item (product or service).
 * @param {Object} item - Product or service item to delete
 */
async function removeProduct(item) {
  const isService = !!item.is_service
  const confirmed = await askConfirm({
    title: isService ? '¿Eliminar Servicio de Taller?' : '¿Eliminar Refacción de Almacén?',
    message: '¿Estás seguro de que deseas eliminar permanentemente del catálogo:',
    itemName: `${item.name} (${item.sku})`,
    itemType: isService ? 'service' : 'product',
    requiresAdmin: false,
    warningText: isService
      ? 'El servicio dejará de estar disponible para seleccionar en nuevas órdenes de trabajo.'
      : 'La refacción se dará de baja del almacén y no podrá agregarse a órdenes.'
  })

  if (!confirmed) return

  try {
    await store.deleteProduct(item.id)
    toast.success(isService ? 'Servicio eliminado correctamente.' : 'Refacción eliminada del almacén.')
  } catch (e) {
    toast.error(e.message)
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

/* Modal Header Custom */
.product-modal-header {
  display: flex;
  align-items: center;
  gap: 14px;
}
.product-modal-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.product-modal-icon.part-icon {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.25), rgba(56, 189, 248, 0.15));
  border: 1px solid rgba(56, 189, 248, 0.3);
  color: var(--blue);
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
}
.product-modal-icon.service-icon {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(52, 211, 153, 0.15));
  border: 1px solid rgba(52, 211, 153, 0.3);
  color: #34d399;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
}
.product-modal-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  line-height: 1.2;
}
.product-modal-subtitle {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 3px;
  line-height: 1.35;
}

/* Modal Body */
.product-modal-body {
  padding: 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 78vh;
  overflow-y: auto;
}

/* Panels */
.product-panel {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 12px;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
}
.product-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}
.product-panel-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #e2e8f0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.product-panel-title svg {
  color: var(--blue);
}
.req-legend {
  font-size: 0.76rem;
  color: var(--text-muted);
}
.req-star {
  color: #f87171;
  font-weight: bold;
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

/* Modal Footer & Buttons */
.product-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 1.25rem 1.75rem;
}
.btn-submit-product {
  padding: 10px 22px;
  font-size: 0.95rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.btn-submit-service {
  padding: 10px 22px;
  font-size: 0.95rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #059669;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
}
.btn-submit-service:hover {
  background: #047857;
  box-shadow: 0 4px 18px rgba(16, 185, 129, 0.5);
  transform: translateY(-1px);
}
</style>