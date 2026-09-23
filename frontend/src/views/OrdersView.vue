<template>
  <div class="orders-page">
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

    <div class="card orders-card" style="padding:0">
      <div class="table-scroll table-scroll-orders" v-if="filteredOrders.length">
        <table class="data-table">
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
            <tr v-for="o in pagedOrders" :key="o.id">
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
                    title="Eliminar Orden de Trabajo"
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
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>
        </div>
        <p>{{ search || statusFilter ? 'No hay órdenes que coincidan con los filtros aplicados.' : 'No hay órdenes de servicio. Crea la primera.' }}</p>
      </div>

      <!-- Barra de Paginación y Selector de Cantidad -->
      <div class="table-pagination" v-if="filteredOrders.length">
        <div class="pagination-left">
          <div class="pagination-size-selector">
            <span>Mostrar:</span>
            <select v-model.number="pageSize" class="pagination-size-select">
              <option :value="10">10 órdenes</option>
              <option :value="20">20 órdenes</option>
              <option :value="50">50 órdenes</option>
              <option :value="100">100 órdenes</option>
            </select>
          </div>
          <span class="pagination-info">
            Mostrando <strong>{{ startRecord }}</strong> a <strong>{{ endRecord }}</strong> de <strong>{{ filteredOrders.length }}</strong> órdenes
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

    <!-- Modal nueva orden -->
    <Modal v-model="showModal" :wide="true">
      <template #header>
        <div class="order-modal-header">
          <div class="order-modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <div>
            <div class="order-modal-title">Nueva Orden de Servicio</div>
            <div class="order-modal-subtitle">Registra cliente, vehículo, diagnóstico y refacciones para cotizar</div>
          </div>
        </div>
      </template>

      <div class="modal-body order-modal-body">
        <!-- Panel 1: Datos del cliente y vehículo -->
        <div class="order-panel">
          <div class="order-panel-header">
            <span class="order-panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Datos del Cliente y Vehículo
            </span>
            <span class="req-legend"><span class="req-star">*</span> Campos obligatorios</span>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label">
                Nombre del Cliente <span class="req-star">*</span>
              </label>
              <input
                v-model="form.customer_name"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submitted && !form.customer_name.trim() }"
                placeholder="Ej. Ing. Juan Pérez / Flotilla Norte"
              />
              <span v-if="submitted && !form.customer_name.trim()" class="form-field-error">
                El nombre del cliente es obligatorio
              </span>
            </div>

            <div class="form-group">
              <label class="form-label">
                Vehículo / Modelo / Placas <span class="req-star">*</span>
              </label>
              <input
                v-model="form.vehicle"
                class="form-input form-input-lg"
                :class="{ 'input-has-error': submitted && !form.vehicle.trim() }"
                placeholder="Ej. Jetta 2018 Rojo (Placas ABC-123)"
              />
              <span v-if="submitted && !form.vehicle.trim()" class="form-field-error">
                El vehículo o modelo a ingresar es obligatorio
              </span>
            </div>
          </div>

          <!-- Cuadro de Observaciones / Detalles -->
          <div class="form-group" style="margin-top: 4px;">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <label class="form-label">
                Observaciones / Detalles del Servicio
              </label>
              <span class="form-label-hint">Diagnóstico previo, extras o notas</span>
            </div>
            <textarea
              v-model="form.notes"
              class="form-textarea"
              rows="3"
              placeholder="Ingresa detalles o peticiones especiales del cliente, diagnósticos previos (ej. rechinido en frenos, check engine, fuga de refrigerante), refacciones especiales o piezas que no estén en la lista..."
            ></textarea>
            <span class="field-hint">
              Esta información se registrará en el expediente de la orden y estará visible para los mecánicos en el taller.
            </span>
          </div>
        </div>

        <!-- Panel 2: Partidas de la Orden (Refacciones y Servicios) -->
        <div class="order-panel">
          <div class="order-panel-header">
            <div style="display:flex;align-items:center;gap:8px">
              <span class="order-panel-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Refacciones y Servicios de Taller
              </span>
              <span class="items-counter-badge">{{ form.items.length }} {{ form.items.length === 1 ? 'partida' : 'partidas' }}</span>
            </div>
            <button class="btn btn-ghost btn-sm add-item-btn" @click="addItem" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Agregar elemento
            </button>
          </div>

          <div class="order-items-list">
            <div v-for="(item, idx) in form.items" :key="idx" class="order-item-row">
              <div class="item-badge">#{{ idx + 1 }}</div>
              
              <div class="item-select-wrap">
                <select
                  v-model.number="item.product_id"
                  class="form-select order-select"
                  :class="{ 'input-has-error': submitted && !item.product_id }"
                >
                  <option value="" disabled>Seleccionar refacción o servicio del catálogo...</option>
                  <optgroup label="SERVICIOS Y MANO DE OBRA (TALLER)">
                    <option v-for="s in services" :key="s.id" :value="s.id">
                      [Servicio] {{ s.name }} — {{ formatCurrency(s.price) }}
                    </option>
                  </optgroup>
                  <optgroup label="REFACCIONES FÍSICAS EN ALMACÉN">
                    <option v-for="p in physicalParts" :key="p.id" :value="p.id" :disabled="p.stock === 0">
                      {{ p.name }} (SKU: {{ p.sku }}) — {{ formatCurrency(p.price) }} {{ p.stock === 0 ? '— [AGOTADO]' : `— Stock: ${p.stock} pzas` }}
                    </option>
                  </optgroup>
                </select>
              </div>

              <div class="item-qty-wrap">
                <input
                  v-model.number="item.qty"
                  type="number"
                  min="1"
                  :max="getItemMaxStock(item)"
                  class="form-input text-center order-qty-input"
                  :class="{ 'input-has-error': isItemOverStock(item) }"
                  placeholder="Cant."
                  title="Cantidad"
                />
                <span v-if="isItemOverStock(item)" class="item-stock-warning" :title="'Solo hay ' + getProductStock(item) + ' pzas disponibles en almacén'">
                  Máx: {{ getProductStock(item) }}
                </span>
              </div>

              <div class="item-subtotal-wrap">
                <span class="item-subtotal-val">{{ formatCurrency(getItemSubtotal(item)) }}</span>
              </div>

              <button
                class="btn-icon del item-del-btn"
                @click="removeItem(idx)"
                :disabled="form.items.length === 1"
                type="button"
                title="Eliminar esta partida"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Total estimado destacado -->
        <div class="order-total-card">
          <div class="order-total-details">
            <span class="order-total-label">TOTAL ESTIMADO DE LA ORDEN</span>
            <span class="order-total-sublabel">Calculado automáticamente con {{ form.items.length }} partida(s) de catálogo</span>
          </div>
          <div class="order-total-display">
            <span class="order-total-currency">MXN</span>
            <span class="order-total-amount">{{ formatCurrency(orderTotal) }}</span>
          </div>
        </div>
      </div>

      <div class="modal-footer order-modal-footer">
        <button class="btn btn-ghost" @click="showModal = false" type="button">Cancelar</button>
        <button class="btn btn-primary btn-submit-order" @click="submitOrder" :disabled="creating" type="button">
          <svg v-if="!creating" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px"><path d="M20 6L9 17l-5-5"/></svg>
          <span>{{ creating ? 'Registrando orden...' : 'Crear orden de servicio' }}</span>
        </button>
      </div>
    </Modal>

    <!-- Modal detalle de orden -->
    <Modal v-model="showDetailModal" :wide="true">
      <template #header>
        <div class="order-modal-header" v-if="selectedOrder">
          <div class="order-modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
          <div>
            <div class="order-modal-title">
              <span>Orden de Servicio #{{ selectedOrder.id }}</span>
              <span class="detail-folio-pill">Folio #{{ selectedOrder.id }}</span>
            </div>
            <div class="order-modal-subtitle">Expediente técnico, refacciones asignadas y desglose financiero</div>
          </div>
        </div>
      </template>

      <div class="modal-body detail-modal-body" v-if="selectedOrder">
        <!-- Tarjeta de Información Principal del Cliente y Vehículo -->
        <div class="detail-header-card">
          <div class="detail-header-main">
            <div class="detail-client-name">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:#38bdf8;flex-shrink:0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span>{{ selectedOrder.customer_name || 'Sin nombre de cliente' }}</span>
            </div>
            <div class="detail-vehicle-desc">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:#94a3b8;flex-shrink:0"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              <span>{{ selectedOrder.vehicle || 'Sin descripción de vehículo' }}</span>
            </div>
            <div class="detail-date-badge" v-if="selectedOrder.created_at">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <span>Ingresado: {{ formatDateTime(selectedOrder.created_at) }}</span>
            </div>
          </div>
          <div class="detail-header-side">
            <StatusBadge :status="selectedOrder.status" />
          </div>
        </div>

        <!-- Observaciones / Detalles si existen -->
        <div v-if="selectedOrder.notes" class="detail-notes-card">
          <div class="detail-notes-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>Observaciones y Diagnóstico de Taller</span>
          </div>
          <div class="detail-notes-body">{{ selectedOrder.notes }}</div>
        </div>

        <!-- Tabla de Partidas con Contenedor Scrollable y Encabezados Sticky -->
        <div class="detail-items-section">
          <div class="detail-section-header">
            <div class="detail-section-title-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:#38bdf8"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
              <h4 class="detail-table-title">Refacciones y Componentes Utilizados</h4>
            </div>
            <span class="detail-count-badge" v-if="selectedOrder.items?.length">
              {{ selectedOrder.items.length }} {{ selectedOrder.items.length === 1 ? 'partida' : 'partidas' }}
            </span>
          </div>

          <div class="detail-table-scroll-container" v-if="selectedOrder.items?.length">
            <table class="data-table detail-items-table">
              <thead>
                <tr>
                  <th style="width: 44%">Refacción / Servicio</th>
                  <th style="width: 18%">SKU / Clave</th>
                  <th class="text-center" style="width: 10%">Cant.</th>
                  <th class="text-right" style="width: 14%">P. Unitario</th>
                  <th class="text-right" style="width: 14%">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="it in selectedOrder.items" :key="it.id">
                  <td>
                    <div class="detail-product-name-wrap">
                      <span v-if="it.product?.is_service" class="service-pill-mini">SERVICIO</span>
                      <span v-else class="part-pill-mini">REFACCIÓN</span>
                      <span class="detail-product-name">{{ it.product?.name || ('Producto #' + it.product_id) }}</span>
                    </div>
                  </td>
                  <td>
                    <code class="sku-badge">{{ it.product?.sku || '—' }}</code>
                  </td>
                  <td class="text-center">
                    <span class="qty-pill">{{ it.quantity || it.qty }}</span>
                  </td>
                  <td class="text-right text-muted font-tabular">
                    {{ formatCurrency(it.unit_price) }}
                  </td>
                  <td class="text-right font-semibold text-accent font-tabular">
                    {{ formatCurrency(it.subtotal) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state" style="padding:1.5rem">
            <p>Esta orden no tiene partidas registradas.</p>
          </div>
        </div>

        <!-- Resumen Financiero Consolidado -->
        <div class="detail-total-card">
          <div class="detail-total-info">
            <div class="detail-total-meta">
              <span>Total de Partidas: <strong>{{ selectedOrder.items?.length || 0 }}</strong></span>
              <span class="detail-meta-dot">•</span>
              <span>Unidades aplicadas: <strong>{{ totalQuantity(selectedOrder) }}</strong></span>
            </div>
            <div class="detail-total-desc">Importe total neto de la orden en moneda nacional (MXN)</div>
          </div>
          <div class="detail-total-amount-box">
            <span class="detail-total-badge">TOTAL NETO</span>
            <span class="detail-total-amount">{{ formatCurrency(selectedOrder.total) }}</span>
          </div>
        </div>
      </div>
      <div class="modal-footer detail-modal-footer">
        <button class="btn btn-ghost" @click="printOrder" type="button" title="Imprimir expediente de la orden">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          <span>Imprimir / Ticket</span>
        </button>
        <button class="btn btn-primary" @click="showDetailModal = false" type="button">
          Cerrar
        </button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
/**
 * @fileoverview Workshop Repair & Service Work Orders View
 * @module views/OrdersView
 * @description Manages the complete lifecycle of customer vehicle repair orders:
 * - Order registration with multi-item catalog picker (parts & labor) and dynamic subtotaling.
 * - Workflow status transitions (open -> in_progress -> done -> delivered).
 * - Detailed inspection modal with client profile, line item table, and printable ticket layout.
 * - Protected order deletion requiring Administrator PIN verification.
 */

import { ref, computed, watch, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import { useConfirm } from '../composables/useConfirm'
import Modal from '../components/ui/Modal.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import SortableTh from '../components/ui/SortableTh.vue'
import { formatCurrency, formatDateTime } from '../utils/format'

/** Global Pinia state store */
const store = useStore()

/** Notification toast dispatcher */
const toast = useToast()

/** Centralized confirmation modal service */
const { askConfirm } = useConfirm()

/**
 * Calculates the cumulative item count of an order.
 * @param {Object} order - Work order object
 * @returns {number} Total units of parts and services
 */
function totalQuantity(order) {
  if (!order?.items?.length) return 0
  return order.items.reduce((sum, it) => sum + parseInt(it.quantity || it.qty || 1, 10), 0)
}

/**
 * Triggers native browser print dialog for the active order ticket.
 */
function printOrder() {
  window.print()
}

/** Fetches orders and product catalog on initial mount */
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
const submitted = ref(false)

/**
 * Toggles column sort field and direction.
 * @param {string} field - Selected property to sort by
 */
function handleSort(field) {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = field === 'id' || field === 'total' ? 'desc' : 'asc'
  }
}

/** Priority weights for sorting orders by status progression */
const statusWeight = { open: 1, in_progress: 2, done: 3, delivered: 4 }

/**
 * Filtered and sorted collection of customer work orders.
 * @type {import('vue').ComputedRef<Array<Object>>}
 */
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

/** Selected pagination page size (10, 20, 50, 100) */
const pageSize = ref(10)

/** Active page number (1-indexed) */
const currentPage = ref(1)

/** Reset active page to 1 whenever filters or page size change */
watch([search, statusFilter, pageSize], () => {
  currentPage.value = 1
})

/** Total number of pagination pages */
const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / pageSize.value) || 1
})

/** Slice of orders for current pagination page */
const pagedOrders = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredOrders.value.slice(start, start + pageSize.value)
})

/** Start record number in current slice */
const startRecord = computed(() => {
  return filteredOrders.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1
})

/** End record number in current slice */
const endRecord = computed(() => {
  return Math.min(currentPage.value * pageSize.value, filteredOrders.value.length)
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

/** Alphabetically sorted labor services catalog */
const services = computed(() =>
  store.products.filter(p => p.is_service).sort((a, b) => a.name.localeCompare(b.name, 'es'))
)

/** Alphabetically sorted physical spare parts catalog */
const physicalParts = computed(() =>
  store.products.filter(p => !p.is_service).sort((a, b) => a.name.localeCompare(b.name, 'es'))
)

/**
 * Opens detailed view modal for the selected work order.
 * @param {Object} o - Work order entity
 */
function openDetail(o) {
  selectedOrder.value = o
  showDetailModal.value = true
}

/**
 * Factory for creating an empty work order payload.
 * @returns {Object} Fresh order form model
 */
const emptyForm = () => ({
  customer_name: '',
  vehicle: '',
  notes: '',
  items: [{ product_id: '', qty: 1 }]
})
const form = ref(emptyForm())

/**
 * Calculates subtotal for a single line item in the creation form.
 * @param {Object} item - Form line item
 * @returns {number} Line item total in MXN
 */
function getItemSubtotal(item) {
  if (!item.product_id) return 0
  const p = store.products.find(x => x.id === item.product_id)
  return p ? parseFloat(p.price) * (parseInt(item.qty, 10) || 0) : 0
}

/** Computed total estimate for the order currently being created */
const orderTotal = computed(() =>
  form.value.items.reduce((sum, item) => sum + getItemSubtotal(item), 0)
)

/**
 * Opens the new work order registration modal.
 */
function openCreate() {
  form.value = emptyForm()
  submitted.value = false
  showModal.value = true
}

/**
 * Adds an empty line item row to the work order form.
 */
function addItem() {
  form.value.items.push({ product_id: '', qty: 1 })
}

/**
 * Removes a specific line item row from the form.
 * @param {number} idx - Index of item to remove
 */
function removeItem(idx) {
  if (form.value.items.length > 1) {
    form.value.items.splice(idx, 1)
  }
}

/**
 * Gets the maximum allowable quantity based on available physical stock.
 * Returns undefined for labor services (is_service = 1).
 * @param {Object} item - Form line item
 * @returns {number|undefined} Available physical stock limit
 */
function getItemMaxStock(item) {
  if (!item.product_id) return undefined
  const p = store.products.find(x => x.id === item.product_id)
  return p && !p.is_service ? Math.max(0, p.stock) : undefined
}

/**
 * Checks if the requested item quantity exceeds physical warehouse inventory.
 * @param {Object} item - Form line item
 * @returns {boolean} True if quantity exceeds stock
 */
function isItemOverStock(item) {
  if (!item.product_id || !item.qty) return false
  const p = store.products.find(x => x.id === item.product_id)
  return !!(p && !p.is_service && item.qty > p.stock)
}

/**
 * Returns the current available stock for the product selected in a line item.
 * @param {Object} item - Form line item
 * @returns {number} Physical stock units
 */
function getProductStock(item) {
  if (!item.product_id) return 0
  const p = store.products.find(x => x.id === item.product_id)
  return p ? Math.max(0, p.stock) : 0
}

/**
 * Validates and creates a new work order.
 */
async function submitOrder() {
  submitted.value = true
  const custName = (form.value.customer_name || '').trim()
  const veh = (form.value.vehicle || '').trim()

  if (!custName) {
    return toast.error('El nombre del cliente es obligatorio para registrar la orden.')
  }
  if (!veh) {
    return toast.error('El vehículo o modelo a ingresar es obligatorio.')
  }

  const valid = form.value.items.filter(i => i.product_id && i.qty > 0)
  if (!valid.length) {
    return toast.error('Debes agregar al menos una refacción o servicio a la orden.')
  }

  const hasEmpty = form.value.items.some(i => !i.product_id)
  if (hasEmpty) {
    return toast.error('Selecciona una refacción o servicio válido en cada partida agregada.')
  }

  // Validación estricta de existencias físicas en almacén
  const requestedTotals = {}
  for (const item of valid) {
    requestedTotals[item.product_id] = (requestedTotals[item.product_id] || 0) + (parseInt(item.qty, 10) || 0)
  }
  for (const [pid, totalQty] of Object.entries(requestedTotals)) {
    const p = store.products.find(x => x.id === Number(pid))
    if (p && !p.is_service && totalQty > p.stock) {
      const disponible = Math.max(0, p.stock)
      return toast.error(`Stock insuficiente para "${p.name}". Solicitaste ${totalQty} pzas en total pero solo hay ${disponible} disponibles en almacén.`)
    }
  }

  creating.value = true
  try {
    await store.createOrder({
      customer_name: custName,
      vehicle: veh,
      notes: (form.value.notes || '').trim() || null,
      items: valid
    })
    toast.success('¡Orden de servicio registrada exitosamente!')
    showModal.value = false
    form.value = emptyForm()
    submitted.value = false
  } catch (e) {
    toast.error(e.message)
  } finally {
    creating.value = false
  }
}

/**
 * Updates the fulfillment status of a work order.
 * @param {Object} order - Order to update
 * @param {'open'|'in_progress'|'done'|'delivered'} status - Target workflow status
 */
async function changeStatus(order, status) {
  try {
    await store.updateOrderStatus(order.id, status)
    toast.success('Estatus actualizado')
  } catch (e) { toast.error(e.message) }
}

/**
 * Prompts confirmation and removes a work order.
 * Enforces Administrator PIN authorization.
 * @param {Object} order - Order to delete
 */
async function removeOrder(order) {
  const confirmed = await askConfirm({
    title: '¿Eliminar Orden de Servicio?',
    message: '¿Estás seguro de que deseas eliminar permanentemente la orden de trabajo:',
    itemName: `Orden #${order.id} — ${order.customer_name} (${order.vehicle})`,
    itemType: 'order',
    requiresAdmin: true,
    warningText: 'Operación restringida de alto valor: La orden será eliminada del historial del taller y del registro operativo.'
  })

  if (!confirmed || (typeof confirmed === 'object' && !confirmed.confirmed)) return

  const pin = typeof confirmed === 'object' ? confirmed.pin : undefined
  try {
    await store.deleteOrder(order.id, pin)
    toast.success(`Orden #${order.id} eliminada exitosamente.`)
  } catch (e) {
    toast.error(e.message)
  }
}
</script>

<style scoped>
/* Modal Header Custom */
.order-modal-header {
  display: flex;
  align-items: center;
  gap: 14px;
}
.order-modal-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.25), rgba(56, 189, 248, 0.15));
  border: 1px solid rgba(56, 189, 248, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--blue);
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
  flex-shrink: 0;
}
.order-modal-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  line-height: 1.2;
}
.order-modal-subtitle {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 3px;
  line-height: 1.35;
}

/* Modal Body */
.order-modal-body {
  padding: 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 78vh;
  overflow-y: auto;
}

/* Panels */
.order-panel {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 12px;
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
}
.order-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}
.order-panel-title {
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #e2e8f0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.order-panel-title svg {
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
.form-label-hint {
  font-size: 0.76rem;
  color: var(--text-muted);
  font-weight: 400;
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

/* Items counter & button */
.items-counter-badge {
  background: rgba(56, 189, 248, 0.12);
  color: var(--blue);
  border: 1px solid rgba(56, 189, 248, 0.25);
  padding: 2px 8px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
}
.add-item-btn {
  background: rgba(37, 99, 235, 0.15);
  border-color: rgba(37, 99, 235, 0.4);
  color: #93c5fd;
  font-size: 0.84rem;
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.add-item-btn:hover {
  background: var(--accent);
  color: #ffffff;
  border-color: var(--accent);
  box-shadow: 0 2px 10px var(--accent-glow);
}

/* Order Item Row */
.order-items-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.order-item-row {
  display: grid;
  grid-template-columns: 36px 1fr 85px 115px 36px;
  gap: 10px;
  align-items: center;
  background: rgba(10, 15, 30, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 10px;
  padding: 8px 10px;
  transition: border-color 0.2s ease, background 0.2s ease;
}
.order-item-row:hover {
  border-color: rgba(56, 189, 248, 0.35);
  background: rgba(14, 22, 44, 0.75);
}
.item-badge {
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--text-muted);
  text-align: center;
  user-select: none;
}
.order-select {
  height: 42px;
  font-size: 0.94rem;
  background: rgba(10, 15, 30, 0.9);
}
.order-qty-input {
  height: 42px;
  font-size: 0.96rem;
  font-weight: 700;
  padding: 0 6px;
}
.item-subtotal-wrap {
  text-align: right;
  padding: 0 4px;
}
.item-subtotal-val {
  font-size: 0.98rem;
  font-weight: 800;
  color: var(--blue);
  white-space: nowrap;
}
.item-del-btn {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  color: #f87171;
  transition: all 0.2s ease;
}
.item-del-btn:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.2);
  color: #ffffff;
}

/* Total Card */
.order-total-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, rgba(17, 25, 46, 0.9), rgba(15, 23, 42, 0.95));
  border: 1px solid rgba(56, 189, 248, 0.25);
  border-radius: 12px;
  padding: 16px 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}
.order-total-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.order-total-label {
  font-size: 0.86rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: #94a3b8;
  text-transform: uppercase;
}
.order-total-sublabel {
  font-size: 0.8rem;
  color: var(--text-muted);
}
.order-total-display {
  display: flex;
  align-items: baseline;
  gap: 6px;
}
.order-total-currency {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-muted);
}
.order-total-amount {
  font-size: 1.6rem;
  font-weight: 800;
  color: #38bdf8;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 12px rgba(56, 189, 248, 0.35);
}

/* Modal Footer & Buttons */
.order-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 1.25rem 1.75rem;
}
.btn-submit-order {
  padding: 10px 22px;
  font-size: 0.95rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Order Detail Modal Specifics */
.detail-folio-pill {
  font-size: 0.72rem;
  font-weight: 700;
  background: rgba(56, 189, 248, 0.14);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.28);
  padding: 2px 8px;
  border-radius: 6px;
  margin-left: 8px;
  letter-spacing: 0.03em;
  display: inline-block;
  vertical-align: middle;
}

.detail-header-card {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.015) 100%);
  border: 1px solid rgba(148, 163, 184, 0.16);
  padding: 1.25rem 1.4rem;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  gap: 16px;
}

.detail-header-main {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.detail-client-name {
  font-size: 1.22rem;
  font-weight: 800;
  color: #f8fafc;
  letter-spacing: -0.015em;
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-vehicle-desc {
  font-size: 0.94rem;
  color: #cbd5e1;
  display: flex;
  align-items: center;
  gap: 7px;
}

.detail-date-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  color: #94a3b8;
  background: rgba(15, 23, 42, 0.6);
  padding: 3px 9px;
  border-radius: 6px;
  border: 1px solid rgba(148, 163, 184, 0.12);
  width: fit-content;
  margin-top: 4px;
}

.detail-header-side {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.detail-notes-card {
  background: rgba(56, 189, 248, 0.05);
  border: 1px solid rgba(56, 189, 248, 0.2);
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.detail-notes-header {
  font-size: 0.8rem;
  font-weight: 700;
  color: #38bdf8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 6px;
}

.detail-notes-body {
  font-size: 0.92rem;
  color: #e2e8f0;
  line-height: 1.55;
  white-space: pre-wrap;
}

.detail-items-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
}

.detail-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-section-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-table-title {
  font-size: 0.98rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0;
}

.detail-count-badge {
  font-size: 0.76rem;
  font-weight: 700;
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  padding: 3px 9px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.detail-table-scroll-container {
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 10px;
  overflow-x: auto;
  overflow-y: auto;
  max-height: 290px;
  background: rgba(15, 23, 42, 0.4);
}

.detail-table-scroll-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.detail-table-scroll-container::-webkit-scrollbar-track {
  background: rgba(15, 23, 42, 0.4);
}
.detail-table-scroll-container::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.25);
  border-radius: 9999px;
}
.detail-table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.45);
}

.detail-items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}

.detail-items-table thead th {
  position: sticky;
  top: 0;
  background: #0b1120;
  z-index: 2;
  padding: 10px 14px;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #94a3b8;
  border-bottom: 1px solid rgba(148, 163, 184, 0.16);
}

.detail-items-table tbody td {
  padding: 10px 14px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.07);
  vertical-align: middle;
}

.detail-product-name-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-product-name {
  color: #f1f5f9;
  font-weight: 600;
}

.service-pill-mini {
  font-size: 0.68rem;
  font-weight: 700;
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid rgba(56, 189, 248, 0.3);
  letter-spacing: 0.03em;
  flex-shrink: 0;
}

.part-pill-mini {
  font-size: 0.68rem;
  font-weight: 700;
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid rgba(16, 185, 129, 0.3);
  letter-spacing: 0.03em;
  flex-shrink: 0;
}

.qty-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 24px;
  padding: 0 6px;
  font-weight: 700;
  font-size: 0.85rem;
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 6px;
}

.font-tabular {
  font-variant-numeric: tabular-nums;
}

.sku-badge {
  background: rgba(148, 163, 184, 0.12);
  color: #cbd5e1;
  padding: 3px 8px;
  border-radius: 5px;
  font-size: 0.82rem;
  font-family: monospace;
}

.detail-total-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(56, 189, 248, 0.06) 100%);
  border: 1px solid rgba(56, 189, 248, 0.25);
  border-radius: 12px;
  padding: 1.1rem 1.4rem;
  margin-top: 6px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  gap: 16px;
  flex-wrap: wrap;
}

.detail-total-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-total-meta {
  font-size: 0.86rem;
  color: #cbd5e1;
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail-meta-dot {
  color: #64748b;
}

.detail-total-desc {
  font-size: 0.78rem;
  color: #94a3b8;
}

.detail-total-amount-box {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.detail-total-badge {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: #38bdf8;
  text-transform: uppercase;
}

.detail-total-amount {
  font-size: 1.6rem;
  font-weight: 850;
  color: #38bdf8;
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
  text-shadow: 0 2px 14px rgba(56, 189, 248, 0.35);
}

.detail-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

/* Full-Height Layout: Expands table frame to fill viewport without dead space */
.orders-page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - var(--header-h) - 4rem);
  min-height: 540px;
}

.orders-card {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.table-scroll-orders {
  flex: 1;
  min-height: 0;
  max-height: none !important;
  overflow-y: auto;
  overflow-x: auto;
}

.orders-card .table-pagination {
  margin-top: auto;
  flex-shrink: 0;
}

.item-stock-warning {
  display: block;
  font-size: 0.68rem;
  font-weight: 700;
  color: #ef4444;
  text-align: center;
  margin-top: 3px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.35);
  border-radius: 4px;
  padding: 1px 4px;
  white-space: nowrap;
}

@media (max-width: 640px) {
  .order-item-row {
    grid-template-columns: 1fr 70px 36px;
  }
  .item-badge {
    display: none;
  }
  .item-subtotal-wrap {
    grid-column: span 3;
    text-align: right;
  }
}
</style>