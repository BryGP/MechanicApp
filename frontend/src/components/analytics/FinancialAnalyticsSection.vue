<template>
  <div class="analytics-wrapper animate-fade-in-up">
    <div class="analytics-header">
      <div class="analytics-title-group">
        <div class="analytics-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
          </svg>
        </div>
        <div>
          <h3 class="analytics-title">Estadísticas & Analítica Operativa</h3>
          <p class="analytics-subtitle">
            Monitoreo en tiempo real de finanzas, absorción de costos, impacto de refacciones y actividad vehicular
          </p>
        </div>
      </div>
      <div class="analytics-meta-badge">
        <span class="pulse-dot"></span>
        Datos en vivo sincronizados
      </div>
    </div>

    <div class="analytics-grid">
      <!-- 1. DISTRIBUCIÓN DE EGRESOS POR CATEGORÍA -->
      <div class="card analytics-card">
        <AnimatedBarChart
          title="Desglose de Gastos por Categoría"
          subtitle="Proporción e impacto financiero de cada rubro operativo"
          :items="expenseCategoryItems"
          :total-override="store.totalExpenses"
        />
        <div class="analytics-card-footer" v-if="topExpenseCategory">
          <span class="footer-label">Mayor impacto:</span>
          <span class="cat-pill" :class="'cat-' + topExpenseCategory.cat">
            {{ topExpenseCategory.label }}
          </span>
          <span class="footer-amount">{{ formatCurrency(topExpenseCategory.amount) }}</span>
        </div>
      </div>

      <!-- 2. BALANCE OPERATIVO Y FLUJO DE EFECTIVO -->
      <div class="card analytics-card">
        <div class="chart-header">
          <div>
            <h4 class="chart-title">Balance Operativo y Absorción</h4>
            <p class="chart-subtitle">Relación entre facturación bruta, gastos absorbidos y utilidad líquida</p>
          </div>
        </div>

        <div class="cash-flow-breakdown">
          <!-- Ingresos -->
          <div class="flow-item">
            <div class="flow-item-header">
              <span class="flow-label">
                <span class="flow-indicator bg-success"></span>
                Ingresos por Servicios (Base 100%)
              </span>
              <span class="flow-val font-semibold text-success">{{ formatCurrency(store.totalRevenue) }}</span>
            </div>
            <div class="flow-bar-track">
              <div
                class="flow-bar-fill bg-success"
                :style="{ width: mounted ? '100%' : '0%' }"
              ></div>
            </div>
          </div>

          <!-- Gastos -->
          <div class="flow-item">
            <div class="flow-item-header">
              <span class="flow-label">
                <span class="flow-indicator bg-danger"></span>
                Gastos Operativos (Absorción: {{ expenseAbsorptionPct.toFixed(1) }}%)
              </span>
              <span class="flow-val font-semibold text-danger">-{{ formatCurrency(store.totalExpenses) }}</span>
            </div>
            <div class="flow-bar-track">
              <div
                class="flow-bar-fill bg-danger"
                :style="{ width: mounted ? `${Math.min(100, expenseAbsorptionPct)}%` : '0%', transitionDelay: '0.15s' }"
              ></div>
            </div>
          </div>

          <!-- Utilidad Neta -->
          <div class="flow-item">
            <div class="flow-item-header">
              <span class="flow-label">
                <span class="flow-indicator" :class="store.netProfit >= 0 ? 'bg-accent' : 'bg-danger'"></span>
                {{ store.netProfit >= 0 ? 'Utilidad Neta Real' : 'Déficit Operativo' }} (Margen: {{ store.marginPct.toFixed(1) }}%)
              </span>
              <span
                class="flow-val font-semibold"
                :class="store.netProfit >= 0 ? 'text-accent' : 'text-danger'"
              >
                {{ formatCurrency(store.netProfit) }}
              </span>
            </div>
            <div class="flow-bar-track">
              <div
                class="flow-bar-fill"
                :class="store.netProfit >= 0 ? 'bg-accent' : 'bg-danger'"
                :style="{
                  width: mounted ? `${Math.min(100, Math.max(0, store.marginPct))}%` : '0%',
                  transitionDelay: '0.3s'
                }"
              ></div>
            </div>
          </div>
        </div>

        <div class="kpi-mini-grid">
          <div class="kpi-mini-box">
            <span class="kpi-mini-label">Retorno por Peso</span>
            <span class="kpi-mini-value" :class="store.netProfit >= 0 ? 'text-success' : 'text-danger'">
              ${{ returnPerPeso.toFixed(2) }} MXN
            </span>
          </div>
          <div class="kpi-mini-box">
            <span class="kpi-mini-label">Gasto Prom. Diario</span>
            <span class="kpi-mini-value text-muted">
              {{ formatCurrency(avgDailyExpense) }}
            </span>
          </div>
        </div>
      </div>

      <!-- 3. ÁREA DE VEHÍCULOS ATENDIDOS Y PRODUCTOS -->
      <div class="card analytics-card">
        <div class="chart-header">
          <div>
            <h4 class="chart-title">Métricas de Vehículos y Servicios</h4>
            <p class="chart-subtitle">Volumen de atención en taller y ticket promedio por auto</p>
          </div>
        </div>

        <!-- Vehicle KPIs -->
        <div class="vehicle-metrics-list">
          <div class="vehicle-metric-row">
            <div class="metric-icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px">
                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 11 2 11.5 2 12v4c0 .6.4 1 1 1h2"/>
                <circle cx="7" cy="17" r="2"/>
                <circle cx="17" cy="17" r="2"/>
              </svg>
            </div>
            <div class="metric-text-group">
              <span class="metric-label">Vehículos en Órdenes</span>
              <span class="metric-number">{{ store.orders.length }} ingresados</span>
            </div>
            <div class="metric-extra">
              <span class="status-pill status-pill-success">{{ activeOrdersCount }} activas</span>
            </div>
          </div>

          <div class="vehicle-metric-row">
            <div class="metric-icon-box" style="color: var(--accent); background: rgba(59, 130, 246, 0.12)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
            <div class="metric-text-group">
              <span class="metric-label">Ticket Promedio por Vehículo</span>
              <span class="metric-number text-accent">{{ formatCurrency(avgTicketPerVehicle) }}</span>
            </div>
            <div class="metric-extra text-muted" style="font-size:0.75rem">
              Por orden de trabajo
            </div>
          </div>

          <div class="vehicle-metric-row">
            <div class="metric-icon-box" style="color: #c084fc; background: rgba(192, 132, 252, 0.12)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              </svg>
            </div>
            <div class="metric-text-group">
              <span class="metric-label">Refacciones vs Mano de Obra</span>
              <span class="metric-number">{{ partsCount }} refacciones en catálogo</span>
            </div>
            <div class="metric-extra text-muted" style="font-size:0.75rem">
              {{ servicesCount }} servicios
            </div>
          </div>
        </div>

        <!-- Mini table of frequent vehicles -->
        <div class="frequent-vehicles-box" v-if="frequentVehicles.length">
          <div class="frequent-title">Vehículos con Mayor Actividad:</div>
          <div class="frequent-tags">
            <span
              v-for="v in frequentVehicles"
              :key="v.name"
              class="vehicle-tag"
            >
              {{ v.name }} <strong>({{ v.count }})</strong>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Financial & Operational Analytics Section Component
 * @module components/analytics/FinancialAnalyticsSection
 * @description Provides rich animated visual analytics covering workshop expenses by category,
 * cash flow absorption rates, return per peso invested, and vehicle throughput.
 */

import { ref, computed, onMounted } from 'vue'
import { useStore } from '../../store'
import { formatCurrency } from '../../utils/format'
import AnimatedBarChart from '../charts/AnimatedBarChart.vue'

const store = useStore()
const mounted = ref(false)

onMounted(() => {
  requestAnimationFrame(() => {
    setTimeout(() => {
      mounted.value = true
    }, 80)
  })
})

/**
 * Category color and label lookup table
 */
const CATEGORY_META = {
  refacciones: { label: 'Refacciones e Insumos', color: '#3b82f6', badgeClass: 'cat-refacciones' },
  nomina: { label: 'Nómina de Mecánicos', color: '#f59e0b', badgeClass: 'cat-nomina' },
  herramientas: { label: 'Herramientas y Equipo', color: '#10b981', badgeClass: 'cat-herramientas' },
  renta: { label: 'Renta del Taller', color: '#ef4444', badgeClass: 'cat-renta' },
  servicios: { label: 'Servicios (Luz/Agua/Net)', color: '#94a3b8', badgeClass: 'cat-servicios' },
  otros: { label: 'Otros Gastos', color: '#a855f7', badgeClass: 'cat-otros' },
}

/**
 * Aggregates all expenses by category for the animated bar chart.
 */
const expenseCategoryItems = computed(() => {
  const totals = {}
  const counts = {}

  store.expenses.forEach((e) => {
    const cat = e.category || 'otros'
    const amt = parseFloat(e.amount) || 0
    totals[cat] = (totals[cat] || 0) + amt
    counts[cat] = (counts[cat] || 0) + 1
  })

  return Object.keys(totals)
    .map((cat) => {
      const meta = CATEGORY_META[cat] || { label: cat, color: 'var(--accent)', badgeClass: '' }
      return {
        id: cat,
        cat,
        label: meta.label,
        amount: totals[cat],
        count: counts[cat],
        color: meta.color,
        badge: meta.label.split(' ')[0],
        badgeClass: meta.badgeClass
      }
    })
    .sort((a, b) => b.amount - a.amount)
})

/**
 * Identifies the category with the highest financial expense.
 */
const topExpenseCategory = computed(() => {
  return expenseCategoryItems.value.length ? expenseCategoryItems.value[0] : null
})

/**
 * Percentage of gross revenue absorbed by operational expenses.
 */
const expenseAbsorptionPct = computed(() => {
  const rev = store.totalRevenue || 0
  const exp = store.totalExpenses || 0
  if (rev <= 0) return exp > 0 ? 100 : 0
  return (exp / rev) * 100
})

/**
 * Net return in pesos per each peso invested in expenses.
 */
const returnPerPeso = computed(() => {
  const exp = store.totalExpenses || 0
  const net = store.netProfit || 0
  if (exp <= 0) return 0
  return (net + exp) / exp
})

/**
 * Average daily expense estimate (based on active date span or 90 days).
 */
const avgDailyExpense = computed(() => {
  const exp = store.totalExpenses || 0
  return exp > 0 ? exp / 90 : 0
})

/**
 * Average ticket generated per work order.
 */
const avgTicketPerVehicle = computed(() => {
  const count = store.orders.length
  if (count <= 0) return 0
  return store.totalRevenue / count
})

/**
 * Number of active open work orders.
 */
const activeOrdersCount = computed(() => {
  return store.orders.filter((o) => o.status === 'open' || o.status === 'in_progress').length
})

/**
 * Physical parts count in catalog.
 */
const partsCount = computed(() => {
  return store.products.filter((p) => !p.is_service).length
})

/**
 * Labor/service catalog items count.
 */
const servicesCount = computed(() => {
  return store.products.filter((p) => p.is_service).length
})

/**
 * Top frequent vehicles attended in the shop.
 */
const frequentVehicles = computed(() => {
  const counts = {}
  store.orders.forEach((o) => {
    if (o.vehicle && o.vehicle.trim()) {
      const v = o.vehicle.trim()
      counts[v] = (counts[v] || 0) + 1
    }
  })

  return Object.keys(counts)
    .map((name) => ({ name, count: counts[name] }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 4)
})
</script>

<style scoped>
.analytics-wrapper {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.analytics-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.analytics-title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.analytics-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  background: var(--accent-glow);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid rgba(59, 130, 246, 0.25);
}

.analytics-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.01em;
}

.analytics-subtitle {
  margin: 2px 0 0;
  font-size: 0.82rem;
  color: var(--text-muted);
}

.analytics-meta-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 9999px;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: var(--success);
  font-size: 0.78rem;
  font-weight: 600;
}

.pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--success);
  box-shadow: 0 0 8px var(--success);
  animation: pulseDot 2s infinite ease-in-out;
}

@keyframes pulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 1.25rem;
}

.analytics-card {
  padding: 1.25rem 1.4rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.chart-header {
  margin-bottom: 1.25rem;
}

.chart-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text);
}

.chart-subtitle {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.analytics-card-footer {
  margin-top: 1.25rem;
  padding-top: 10px;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
}

.footer-label {
  color: var(--text-muted);
}

.footer-amount {
  margin-left: auto;
  font-weight: 700;
  color: var(--text);
}

/* Cash Flow Breakdown Styles */
.cash-flow-breakdown {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.flow-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.flow-item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.84rem;
}

.flow-label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text);
}

.flow-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.bg-success { background-color: var(--success) !important; }
.bg-danger  { background-color: var(--danger) !important; }
.bg-accent  { background-color: var(--accent) !important; }

.flow-bar-track {
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.055);
  border-radius: 9999px;
  overflow: hidden;
}

.flow-bar-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 1.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.kpi-mini-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 1.25rem;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

.kpi-mini-box {
  background: rgba(15, 23, 42, 0.45);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.kpi-mini-label {
  font-size: 0.72rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 600;
}

.kpi-mini-value {
  font-size: 0.95rem;
  font-weight: 700;
}

/* Vehicle & Parts Metrics */
.vehicle-metrics-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.vehicle-metric-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: rgba(15, 23, 42, 0.35);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
}

.metric-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(16, 185, 129, 0.12);
  color: var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.metric-text-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.metric-label {
  font-size: 0.74rem;
  color: var(--text-muted);
  font-weight: 500;
}

.metric-number {
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--text);
}

.metric-extra {
  margin-left: auto;
  flex-shrink: 0;
}

.status-pill {
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 0.72rem;
  font-weight: 600;
}

.status-pill-success {
  background: rgba(16, 185, 129, 0.15);
  color: var(--success);
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.frequent-vehicles-box {
  margin-top: 1rem;
  padding-top: 10px;
  border-top: 1px solid var(--border);
}

.frequent-title {
  font-size: 0.76rem;
  color: var(--text-muted);
  font-weight: 600;
  margin-bottom: 6px;
}

.frequent-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.vehicle-tag {
  padding: 3px 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.74rem;
  color: var(--text);
}

.vehicle-tag strong {
  color: var(--accent);
}
</style>
