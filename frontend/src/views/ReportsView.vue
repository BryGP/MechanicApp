<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Centro de Reportería</h2>
        <p class="page-subtitle">Detección automática de consultas SQL y analítica gerencial del taller</p>
      </div>
      <div class="tabs-nav">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'reports' }"
          @click="activeTab = 'reports'"
        >
          Reportes Disponibles ({{ reports.length }})
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

    <!-- TAB 1: CATÁLOGO DE REPORTES AUTO-DETECTADOS -->
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
            <code class="report-file">{{ r.file }}</code>
            <button class="btn btn-primary btn-sm" :disabled="loadingReportId === r.id">
              {{ loadingReportId === r.id ? 'Generando...' : 'Generar' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Estado si no hay reportes en la carpeta -->
      <div class="card" v-if="!reports.length && !loadingReports">
        <div class="empty-state">
          <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:36px;height:36px"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <p>No se encontraron reportes en <code>backend/app/Reports/</code></p>
          <p class="text-muted" style="font-size: 0.85rem">
            Crea un archivo como <code>StockCriticoReport.php</code> en esa carpeta para que aparezca aquí automáticamente.
          </p>
        </div>
      </div>

      <!-- Visor de Resultados del Reporte Activo -->
      <div class="card" style="padding:0" v-if="activeResult">
        <div class="results-header">
          <div>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="active-report-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              </div>
              <div>
                <h3 style="font-size:1.05rem;font-weight:700">{{ activeResult.report?.title }}</h3>
                <p class="text-muted" style="font-size:0.8rem">
                  {{ activeResult.count }} registros encontrados | Archivo: <code>{{ activeReport?.file }}</code>
                </p>
              </div>
            </div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-ghost btn-sm" @click="showSqlModal = !showSqlModal">
              {{ showSqlModal ? 'Ocultar SQL' : 'Ver SQL' }}
            </button>
            <button class="btn btn-ghost btn-sm" @click="exportJson">Copiar JSON</button>
          </div>
        </div>

        <!-- Cuadro desplegable de la consulta SQL ejecutada -->
        <transition name="sql-slide">
          <div v-if="showSqlModal" class="sql-box">
            <div class="sql-box-header">CONSULTA SQL EJECUTADA EN MYSQL:</div>
            <pre><code>{{ activeResult.report?.query }}</code></pre>
          </div>
        </transition>

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
          <p>La consulta se ejecutó con éxito pero no devolvió filas.</p>
        </div>
      </div>
    </div>

    <!-- TAB 2: RESUMEN EJECUTIVO -->
    <div v-if="activeTab === 'executive'">
      <div class="stats-grid">
        <StatCard
          icon="tag"
          label="Valor del Almacén en Piezas"
          :value="'$' + inventoryValuation.toFixed(2)"
          color="var(--blue)"
        />
        <StatCard
          icon="box"
          label="Total Refacciones en Stock"
          :value="totalUnitsInStock"
          color="var(--success)"
        />
        <StatCard
          icon="revenue"
          label="Ticket Promedio por Servicio"
          :value="'$' + averageTicket.toFixed(2)"
          color="var(--accent)"
        />
      </div>

      <div class="dash-grid">
        <!-- Top Refacciones -->
        <div class="card">
          <div class="section-header">
            <span class="section-title">Top Refacciones Más Utilizadas</span>
            <span class="text-muted" style="font-size:0.8rem">En órdenes de taller</span>
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
                <td class="text-accent font-semibold">${{ tp.revenue.toFixed(2) }}</td>
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
            <span class="section-title">Estatus Operativo del Taller</span>
            <span class="text-muted" style="font-size:0.8rem">Flujo de bahías</span>
          </div>
          <div class="status-summary-list">
            <div class="status-row" v-for="st in statusBreakdown" :key="st.status">
              <div style="display:flex;align-items:center;gap:8px">
                <StatusBadge :status="st.status" />
                <span class="font-semibold">{{ st.count }} vehículos</span>
              </div>
              <span class="text-muted">${{ st.total.toFixed(2) }}</span>
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

const store = useStore()
const toast = useToast()

const activeTab = ref('reports')
const loadingReports = ref(false)
const loadingReportId = ref(null)
const reports = ref([])
const activeReport = ref(null)
const activeResult = ref(null)
const showSqlModal = ref(false)

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
    toast.error('Error al detectar reportes en backend')
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

function exportJson() {
  if (!activeResult.value?.data) return
  navigator.clipboard.writeText(JSON.stringify(activeResult.value.data, null, 2))
  toast.success('Datos exportados al portapapeles en formato JSON')
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

.sql-box {
  background: #050811;
  border-bottom: 1px solid var(--border);
  padding: 1rem 1.25rem;
  font-family: 'Consolas', 'Courier New', monospace;
  font-size: 0.8rem;
  color: #7dd3fc;
  overflow-x: auto;
}

.sql-slide-enter-active, .sql-slide-leave-active {
  transition: opacity 0.22s var(--ease-spring), transform 0.22s var(--ease-spring);
}
.sql-slide-enter-from, .sql-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.sql-box-header {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.table-responsive {
  max-height: 500px;
  overflow: auto;
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
  gap: 12px;
}

.status-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: var(--bg-hover);
  border-radius: var(--radius-sm);
}

@media (max-width: 900px) {
  .dash-grid {
    grid-template-columns: 1fr;
  }
}
</style>
