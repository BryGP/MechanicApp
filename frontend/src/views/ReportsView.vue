<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Centro de Reportes</h2>
        <p class="page-subtitle">Informes gerenciales, control operativo y analítica del taller</p>
      </div>
      <div class="tabs-nav">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'reports' }"
          @click="activeTab = 'reports'"
        >
          Catálogo de Reportes ({{ reports.length }})
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'executive' }"
          @click="activeTab = 'executive'"
        >
          Resumen Ejecutivo
        </button>
      </div>
    </div>

    <!-- TAB 1: CATÁLOGO DE REPORTES -->
    <div v-if="activeTab === 'reports'">
      <!-- Grid de Tarjetas de Reportes -->
      <div class="reports-grid" style="margin-bottom: 1.5rem">
        <div
          v-for="r in reports"
          :key="r.id"
          class="report-card"
          :class="{ 'report-card--active': activeReport?.id === r.id }"
          @click="selectAndRunReport(r)"
        >
          <div class="report-header">
            <div class="report-icon">
              <svg v-if="r.category === 'Inventario'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-svg">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
              <svg v-else-if="r.category === 'Ventas'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-svg">
                <line x1="18" y1="20" x2="18" y2="10"/>
                <line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              <svg v-else-if="r.category === 'Operaciones'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-svg">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
              </svg>
              <svg v-else-if="r.category === 'Finanzas'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-svg">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-svg">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
            </div>
            <span class="report-cat-badge">{{ r.category || 'General' }}</span>
          </div>
          <h3 class="report-title">{{ r.title }}</h3>
          <p class="report-desc">{{ r.description }}</p>
          <div class="report-footer">
            <span class="report-tag">{{ r.category }}</span>
            <button class="btn btn-primary btn-sm" :disabled="loadingReportId === r.id">
              {{ loadingReportId === r.id ? 'Generando...' : 'Generar' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Estado si no hay reportes -->
      <div class="card" v-if="!reports.length && !loadingReports">
        <div class="empty-state">
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <p>No se encontraron reportes configurados en el sistema.</p>
          <p class="text-muted" style="font-size: 0.85rem">
            Los reportes se configuran en el catálogo del sistema para aparecer aquí automáticamente.
          </p>
        </div>
      </div>

      <!-- Visor de Resultados del Reporte Activo -->
      <div class="card" style="padding:0" v-if="activeResult">
        <div class="results-header">
          <div>
            <div style="display:flex;align-items:center;gap:12px">
              <div class="active-report-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              </div>
              <div>
                <h3 style="font-size:1.05rem;font-weight:700">{{ activeResult.report?.title }}</h3>
                <p class="text-muted" style="font-size:0.8rem;margin-top:2px">
                  {{ activeResult.count }} registros encontrados &bull; Categoría: <span class="font-semibold text-accent">{{ activeResult.report?.category }}</span>
                </p>
              </div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <button class="btn btn-ghost btn-sm" @click="exportData" title="Copiar tabla para pegar en Excel o Google Sheets">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              Copiar Datos
            </button>
            <a :href="`${apiBase}/reports/${activeReport.id}/pdf`" target="_blank" class="btn btn-primary btn-sm" v-if="activeReport">
              <svg viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 17v-2h8v2H8zm0-4v-2h8v2H8zm0-4V7h5v2H8z"/></svg>
              Descargar PDF
            </a>
          </div>
        </div>

        <!-- Tabla dinámica -->
        <div class="table-responsive" v-if="activeResult.data?.length">
          <table class="data-table">
            <thead>
              <tr>
                <th v-for="col in resultColumns" :key="col">{{ formatColName(col) }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rIdx) in activeResult.data" :key="rIdx">
                <td v-for="col in resultColumns" :key="col">
                  {{ row[col] !== null ? row[col] : '—' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="empty-state" v-else>
          <p>El reporte se procesó con éxito pero no devolvió filas.</p>
        </div>
      </div>
    </div>

    <!-- TAB 2: RESUMEN EJECUTIVO -->
    <div v-if="activeTab === 'executive'">
      <div class="stats-grid">
        <StatCard
          icon="tag"
          label="Valor del Almacén en Piezas"
          :value="formatCurrency(inventoryValuation)"
          color="var(--blue)"
        />
        <StatCard
          icon="box"
          label="Total de Refacciones en Stock"
          :value="totalUnitsInStock + ' piezas'"
          color="var(--accent)"
        />
        <StatCard
          icon="revenue"
          label="Ticket Promedio por Servicio"
          :value="formatCurrency(averageTicket)"
          color="var(--success)"
        />
      </div>

      <div class="dash-grid">
        <!-- Top Refacciones -->
        <div class="card">
          <div class="section-header">
            <div>
              <h3 class="section-title">Top Refacciones Más Utilizadas</h3>
              <p class="section-subtitle">Piezas con mayor rotación en servicios</p>
            </div>
          </div>
          <table class="data-table" v-if="topProducts.length">
            <thead>
              <tr>
                <th>Refacción</th>
                <th>Piezas Usadas</th>
                <th>Total Facturado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tp in topProducts" :key="tp.id">
                <td class="font-semibold">{{ tp.name }}</td>
                <td><code style="background:var(--bg-hover);padding:2px 7px;border-radius:4px">{{ tp.usedQty }} pzas</code></td>
                <td class="text-accent font-semibold">{{ formatCurrency(tp.revenue) }}</td>
              </tr>
            </tbody>
          </table>
          <div class="empty-state" v-else>
            <p>No hay órdenes registradas con refacciones aún.</p>
          </div>
        </div>

        <!-- Resumen de Órdenes por Estatus -->
        <div class="card">
          <div class="section-header">
            <div>
              <h3 class="section-title">Estatus Operativo del Taller</h3>
              <p class="section-subtitle">Distribución y flujo por bahías</p>
            </div>
          </div>
          <div class="status-summary-list">
            <div class="status-row" v-for="st in statusBreakdown" :key="st.status">
              <div class="status-row-left">
                <StatusBadge :status="st.status" />
                <span class="status-count">{{ st.count }} {{ st.count === 1 ? 'vehículo' : 'vehículos' }}</span>
              </div>
              <span class="status-amount">{{ formatCurrency(st.total) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import StatCard from '../components/ui/StatCard.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import { formatCurrency } from '../utils/format'

const store = useStore()
const apiBase = import.meta.env.VITE_API_URL
const toast = useToast()

const activeTab = ref('reports')
const loadingReports = ref(false)
const loadingReportId = ref(null)
const reports = ref([])
const activeReport = ref(null)
const activeResult = ref(null)

onMounted(async () => {
  await store.fetchProducts()
  await store.fetchOrders()
  await loadReportsList()
})

async function loadReportsList() {
  loadingReports.value = true
  try {
    await store.fetchReports()
    reports.value = store.reportTemplates
    if (reports.value.length && !activeReport.value) {
      await selectAndRunReport(reports.value[0])
    }
  } catch (err) {
    toast.error('No se pudieron cargar los reportes del taller')
  } finally {
    loadingReports.value = false
  }
}

async function selectAndRunReport(r) {
  activeReport.value = r
  loadingReportId.value = r.id
  try {
    const data = await store.runReport(r.id)
    activeResult.value = data
  } catch (err) {
    toast.error(err.message || 'Error al ejecutar reporte')
  } finally {
    loadingReportId.value = null
  }
}

const resultColumns = computed(() => {
  if (!activeResult.value?.data || !activeResult.value.data.length) return []
  return Object.keys(activeResult.value.data[0])
})

function formatColName(name) {
  return name.replace(/_/g, ' ').toUpperCase()
}

function exportData() {
  if (!activeResult.value?.data || !activeResult.value.data.length) return
  const cols = resultColumns.value
  const header = cols.map(c => formatColName(c)).join('\t')
  const rows = activeResult.value.data.map(row =>
    cols.map(c => (row[c] !== null && row[c] !== undefined ? row[c] : '')).join('\t')
  )
  const tsv = [header, ...rows].join('\n')
  navigator.clipboard.writeText(tsv)
  toast.success('Datos copiados al portapapeles (listos para pegar en Excel)')
}

// Métricas ejecutivas
const inventoryValuation = computed(() =>
  store.products.reduce((sum, p) => sum + parseFloat(p.price || 0) * (p.stock || 0), 0)
)
const totalUnitsInStock = computed(() =>
  store.products.reduce((sum, p) => sum + (p.stock || 0), 0)
)
const averageTicket = computed(() => {
  if (!store.orders.length) return 0
  return store.totalRevenue / store.orders.length
})

const topProducts = computed(() => {
  const map = {}
  store.orders.forEach((o) => {
    ;(o.items || []).forEach((it) => {
      const pId = it.product_id
      const pName = it.product?.name || 'Refacción #' + pId
      const qty = parseInt(it.quantity || it.qty || 1, 10)
      const sub = parseFloat(it.subtotal || 0)
      if (!map[pId]) map[pId] = { id: pId, name: pName, usedQty: 0, revenue: 0 }
      map[pId].usedQty += qty
      map[pId].revenue += sub
    })
  })
  return Object.values(map)
    .sort((a, b) => b.usedQty - a.usedQty)
    .slice(0, 5)
})

const statusBreakdown = computed(() => {
  const map = {
    open: { status: 'open', count: 0, total: 0 },
    in_progress: { status: 'in_progress', count: 0, total: 0 },
    done: { status: 'done', count: 0, total: 0 },
    delivered: { status: 'delivered', count: 0, total: 0 },
  }
  store.orders.forEach((o) => {
    if (map[o.status]) {
      map[o.status].count++
      map[o.status].total += parseFloat(o.total || 0)
    }
  })
  return Object.values(map)
})
</script>

<style scoped>
.tabs-nav {
  display: flex;
  gap: 8px;
  background: var(--bg-card);
  padding: 4px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
}
.tab-btn {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: var(--text-muted);
  font-family: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition);
}
.tab-btn.active {
  background: var(--accent);
  color: #ffffff;
}
.tab-btn:hover:not(.active) {
  background: var(--bg-hover);
  color: var(--text);
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.25rem;
}

.report-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  transition: all var(--transition);
}

.report-card:hover {
  transform: translateY(-2px);
  border-color: var(--accent);
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

.report-card--active {
  border-color: var(--accent);
  box-shadow: 0 0 0 2px var(--accent-glow);
  background: #111a33;
}

.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.report-icon {
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: #0b1e38;
  color: #60a5fa;
  border: 1px solid #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-svg {
  width: 20px;
  height: 20px;
}

.active-report-badge {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #0b1e38;
  color: #60a5fa;
  border: 1px solid #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
}

.report-cat-badge {
  padding: 2px 9px;
  background: #0b1e38;
  color: #60a5fa;
  border: 1px solid #2563eb;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.report-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.5rem;
  line-height: 1.3;
}

.report-desc {
  font-size: 0.8rem;
  color: var(--text-muted);
  line-height: 1.4;
  margin-bottom: 1rem;
  flex-grow: 1;
}

.report-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border);
  padding-top: 0.75rem;
  gap: 8px;
}

.report-file {
  font-size: 0.72rem;
  color: var(--text-muted);
  background: var(--bg-hover);
  padding: 2px 6px;
  border-radius: 4px;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
  gap: 1rem;
}

.table-responsive {
  max-height: 500px;
  overflow: auto;
}

.report-tag {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 500;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.dash-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.status-summary-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  transition: all var(--transition);
}

.status-row:hover {
  border-color: rgba(148, 163, 184, 0.25);
  background: #192748;
}

.status-row-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-count {
  font-size: 0.875rem;
  color: var(--text);
  font-weight: 500;
}

.status-amount {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

@media (max-width: 900px) {
  .dash-grid {
    grid-template-columns: 1fr;
  }
}
</style>
