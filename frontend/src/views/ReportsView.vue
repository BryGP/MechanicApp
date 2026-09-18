<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Centro de Reportería & SQL</h2>
        <p class="page-subtitle">Métricas gerenciales y ejecutor interactivo de plantillas personalizadas en MySQL</p>
      </div>
      <div class="tabs-nav">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'templates' }"
          @click="activeTab = 'templates'"
        >
          ⚡ Plantillas & Consultas MySQL
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'executive' }"
          @click="activeTab = 'executive'"
        >
          📊 Reportes Ejecutivos
        </button>
      </div>
    </div>

    <!-- TAB 1: PLANTILLAS SQL INTERACTIVAS -->
    <div v-if="activeTab === 'templates'">
      <div class="card" style="margin-bottom: 1.5rem">
        <div class="section-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700">Hub de Plantillas MySQL</h3>
            <p class="text-muted" style="font-size: 0.85rem">
              Selecciona una plantilla predefinida o escribe tu propia consulta para ejecutarla en XAMPP
            </p>
          </div>
          <button class="btn btn-ghost btn-sm" @click="openNewTemplateModal">+ Guardar Nueva Plantilla</button>
        </div>

        <!-- Selector de plantillas -->
        <div class="form-row" style="margin-bottom: 1rem">
          <div class="form-group" style="flex: 2">
            <label class="form-label">Plantilla de Reporte</label>
            <select v-model="selectedTemplateId" @change="onSelectTemplate" class="form-select">
              <option v-for="t in allTemplates" :key="t.id" :value="t.id">
                [{{ t.category.toUpperCase() }}] {{ t.title }}
              </option>
            </select>
          </div>
        </div>

        <p v-if="currentTemplate?.description" class="template-desc">
          &#128161; <strong>Objetivo:</strong> {{ currentTemplate.description }}
        </p>

        <!-- Editor SQL -->
        <div class="sql-editor-container">
          <div class="editor-header">
            <span>CONSULTA SQL</span>
            <button class="copy-btn" @click="copySql" title="Copiar consulta SQL">&#128203; Copiar</button>
          </div>
          <textarea
            v-model="sqlQuery"
            class="sql-textarea"
            rows="7"
            spellcheck="false"
            placeholder="SELECT * FROM products..."
          ></textarea>
        </div>

        <div class="editor-footer">
          <span class="text-muted" style="font-size: 0.8rem">
            Se ejecuta directamente en el motor MySQL de XAMPP (modo solo lectura SELECT).
          </span>
          <button class="btn btn-primary" @click="executeCurrentQuery" :disabled="executing">
            {{ executing ? 'Ejecutando en MySQL...' : '⚡ Ejecutar Plantilla en MySQL' }}
          </button>
        </div>

        <!-- Error banner si falla -->
        <div v-if="queryError" class="error-banner">
          ⚠️ <strong>Error en MySQL:</strong> {{ queryError }}
        </div>
      </div>

      <!-- Resultados dinámicos de la consulta -->
      <div class="card" style="padding:0" v-if="queryResults">
        <div class="results-header">
          <div style="font-size:0.95rem;font-weight:700">
            Resultados de la consulta ({{ queryResults.length }} registros)
          </div>
          <button class="btn btn-ghost btn-sm" @click="exportJson">&#128190; Copiar JSON</button>
        </div>

        <div class="table-responsive" v-if="queryResults.length">
          <table class="data-table">
            <thead>
              <tr>
                <th v-for="col in resultColumns" :key="col">{{ formatColName(col) }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rIdx) in queryResults" :key="rIdx">
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

    <!-- TAB 2: REPORTES EJECUTIVOS VISUALES -->
    <div v-if="activeTab === 'executive'">
      <div class="stats-grid">
        <StatCard
          icon="&#127991;"
          label="Valor del Almacén en Piezas"
          :value="'$' + inventoryValuation.toFixed(2)"
          color="var(--blue)"
        />
        <StatCard
          icon="&#128295;"
          label="Total Refacciones en Stock"
          :value="totalUnitsInStock"
          color="var(--success)"
        />
        <StatCard
          icon="&#128176;"
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

    <!-- Modal Guardar Nueva Plantilla -->
    <Modal v-model="showTemplateModal" title="Guardar Nueva Plantilla SQL">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Título del reporte *</label>
          <input v-model="newTemplate.title" class="form-input" placeholder="Ej. Margen por tipo de vehículo" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Categoría *</label>
            <select v-model="newTemplate.category" class="form-select">
              <option value="inventario">Inventario</option>
              <option value="ventas">Ventas y Órdenes</option>
              <option value="contabilidad">Contabilidad</option>
              <option value="operaciones">Operaciones</option>
            </select>
          </div>
          <div class="form-group" style="flex:2">
            <label class="form-label">Descripción breve</label>
            <input v-model="newTemplate.description" class="form-input" placeholder="Para qué sirve este reporte..." />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Consulta SQL (SELECT) *</label>
          <textarea
            v-model="newTemplate.sql_query"
            class="sql-textarea"
            rows="6"
            placeholder="SELECT ... FROM ..."
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost" @click="showTemplateModal = false">Cancelar</button>
        <button class="btn btn-primary" @click="saveCustomTemplate">Guardar plantilla</button>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from '../store'
import { useToast } from '../composables/useToast'
import StatCard from '../components/ui/StatCard.vue'
import StatusBadge from '../components/ui/StatusBadge.vue'
import Modal from '../components/ui/Modal.vue'

const store = useStore()
const toast = useToast()

const activeTab = ref('templates')
const executing = ref(false)
const queryError = ref(null)
const queryResults = ref(null)
const showTemplateModal = ref(false)

// Plantillas SQL maestras precargadas para taller automotriz
const defaultTemplates = [
  {
    id: 'crit-stock',
    title: 'Semáforo de stock crítico y piezas sugeridas a comprar',
    category: 'inventario',
    description: 'Calcula qué refacciones están agotadas o en nivel bajo y cuántas piezas se deben pedir al proveedor.',
    sql_query: `SELECT 
    sku,
    name AS producto,
    stock AS stock_actual,
    min_stock AS stock_minimo,
    CASE 
        WHEN stock = 0 THEN 'AGOTADO'
        WHEN stock <= min_stock THEN 'STOCK CRITICO'
        ELSE 'OK'
    END AS semaforo,
    CASE 
        WHEN stock <= min_stock THEN (min_stock - stock) + 5
        ELSE 0
    END AS sugerido_comprar
FROM products
ORDER BY stock ASC;`,
  },
  {
    id: 'top-parts',
    title: 'Top refacciones más utilizadas y recaudación generada',
    category: 'ventas',
    description: 'Muestra las refacciones con mayor demanda en el taller, en cuántas órdenes aparecen y el total ingresado.',
    sql_query: `SELECT 
    p.sku,
    p.name AS refaccion,
    COUNT(DISTINCT oi.order_id) AS ordenes_en_que_aparece,
    SUM(oi.quantity) AS total_piezas_usadas,
    SUM(oi.subtotal) AS ingreso_generado
FROM products p
INNER JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.sku, p.name
ORDER BY total_piezas_usadas DESC;`,
  },
  {
    id: 'dormant-parts',
    title: 'Refacciones dormidas sin rotación (Capital estancado)',
    category: 'inventario',
    description: 'Detecta piezas que tienes en almacén pero nunca se han instalado en ninguna orden de servicio.',
    sql_query: `SELECT 
    p.sku,
    p.name AS refaccion_sin_movimiento,
    p.stock AS piezas_paradas,
    p.price AS precio_unitario,
    ROUND(p.stock * p.price, 2) AS dinero_estancado
FROM products p
WHERE NOT EXISTS (
    SELECT 1 FROM order_items oi WHERE oi.product_id = p.id
)
ORDER BY dinero_estancado DESC;`,
  },
  {
    id: 'order-ranking',
    title: 'Ranking de órdenes por importe y porcentaje del taller',
    category: 'ventas',
    description: 'Ránking de vehículos reparados con cálculo analítico del porcentaje que representa del total del taller.',
    sql_query: `SELECT 
    o.id AS orden_id,
    o.customer_name AS cliente,
    o.status AS estatus,
    o.total AS monto_orden,
    ROUND((o.total / (SELECT SUM(total) FROM orders)) * 100, 2) AS pct_del_total_taller,
    DENSE_RANK() OVER (ORDER BY o.total DESC) AS ranking
FROM orders o
ORDER BY ranking ASC;`,
  },
  {
    id: 'consolidated-repairs',
    title: 'Reporte integral de diagnósticos con refacciones consolidadas',
    category: 'operaciones',
    description: 'Muestra cada vehículo con la lista concatenada de piezas instaladas en una sola fila utilizando GROUP_CONCAT.',
    sql_query: `SELECT 
    o.id AS orden_id,
    o.customer_name AS cliente,
    o.vehicle AS vehiculo_diagnostico,
    o.status AS estatus,
    COUNT(oi.id) AS tipos_de_refaccion,
    SUM(oi.quantity) AS total_piezas_instaladas,
    o.total AS costo_total_orden,
    GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name, ' ($', oi.subtotal, ')') SEPARATOR ' | ') AS refacciones_utilizadas
FROM orders o
INNER JOIN order_items oi ON o.id = oi.order_id
INNER JOIN products p ON oi.product_id = p.id
GROUP BY o.id, o.customer_name, o.vehicle, o.status, o.total
ORDER BY o.id DESC;`,
  },
  {
    id: 'kpi-summary',
    title: 'Tablero ejecutivo de KPIs del taller en una fila',
    category: 'contabilidad',
    description: 'Resumen gerencial consolidado: órdenes activas, terminadas, facturación total y ticket promedio.',
    sql_query: `SELECT 
    COUNT(id) AS total_ordenes_historicas,
    SUM(CASE WHEN status IN ('open', 'in_progress') THEN 1 ELSE 0 END) AS vehiculos_en_taller_activos,
    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS listos_para_entrega,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS entregados,
    ROUND(SUM(total), 2) AS facturacion_bruta_total,
    ROUND(AVG(total), 2) AS ticket_promedio_por_auto,
    ROUND(MAX(total), 2) AS orden_mas_costosa,
    ROUND(MIN(total), 2) AS orden_mas_economica
FROM orders;`,
  },
]

const customTemplates = ref([])
const allTemplates = computed(() => [...defaultTemplates, ...customTemplates.value])
const selectedTemplateId = ref('crit-stock')
const currentTemplate = computed(() => allTemplates.value.find((t) => t.id === selectedTemplateId.value))
const sqlQuery = ref(defaultTemplates[0].sql_query)

const newTemplate = ref({
  title: '',
  category: 'ventas',
  description: '',
  sql_query: '',
})

onMounted(async () => {
  await store.fetchProducts()
  await store.fetchOrders()
  try {
    await store.fetchReportTemplates()
    if (store.reportTemplates && store.reportTemplates.length) {
      customTemplates.value = store.reportTemplates
    }
  } catch {
    // Si aún no está listo el endpoint del backend, se usan las plantillas maestras locales
  }
})

function onSelectTemplate() {
  if (currentTemplate.value) {
    sqlQuery.value = currentTemplate.value.sql_query
    queryError.value = null
  }
}

function copySql() {
  navigator.clipboard.writeText(sqlQuery.value)
  toast.success('Consulta copiada al portapapeles')
}

async function executeCurrentQuery() {
  if (!sqlQuery.value.trim()) return toast.error('Ingresa una consulta SQL para ejecutar')
  executing.value = true
  queryError.value = null
  try {
    const res = await store.executeSqlReport(sqlQuery.value)
    queryResults.value = res.data || res
    toast.success('Consulta ejecutada con éxito')
  } catch (err) {
    queryError.value = err.message
    toast.error('Error al ejecutar la consulta en MySQL')
  } finally {
    executing.value = false
  }
}

const resultColumns = computed(() => {
  if (!queryResults.value || !queryResults.value.length) return []
  return Object.keys(queryResults.value[0])
})

function formatColName(name) {
  return name.replace(/_/g, ' ').toUpperCase()
}

function exportJson() {
  if (!queryResults.value) return
  navigator.clipboard.writeText(JSON.stringify(queryResults.value, null, 2))
  toast.success('JSON copiado al portapapeles')
}

function openNewTemplateModal() {
  newTemplate.value = {
    title: '',
    category: 'ventas',
    description: '',
    sql_query: sqlQuery.value,
  }
  showTemplateModal.value = true
}

async function saveCustomTemplate() {
  if (!newTemplate.value.title.trim() || !newTemplate.value.sql_query.trim()) {
    return toast.error('Título y consulta SQL son requeridos')
  }
  try {
    await store.createReportTemplate(newTemplate.value)
    toast.success('Plantilla guardada en base de datos')
  } catch {
    // Fallback local
    customTemplates.value.push({
      id: 'custom-' + Date.now(),
      ...newTemplate.value,
    })
    toast.success('Plantilla guardada localmente')
  }
  showTemplateModal.value = false
}

// Métricas ejecutivas computadas
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

.template-desc {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 1rem;
  background: var(--bg-hover);
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  border-left: 3px solid var(--accent);
}

.sql-editor-container {
  background: #060a14;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  overflow: hidden;
  margin-bottom: 1rem;
}
.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 12px;
  background: #090e1c;
  border-bottom: 1px solid var(--border);
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.05em;
}
.copy-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 0.75rem;
  cursor: pointer;
}
.copy-btn:hover {
  color: var(--text);
}

.sql-textarea {
  width: 100%;
  padding: 12px;
  background: transparent;
  border: none;
  color: #7dd3fc;
  font-family: 'Consolas', 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  outline: none;
  resize: vertical;
}

.editor-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.error-banner {
  margin-top: 1rem;
  padding: 10px 14px;
  background: #2f0911;
  color: #f87171;
  border: 1px solid #dc2626;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}

.table-responsive {
  max-height: 450px;
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
