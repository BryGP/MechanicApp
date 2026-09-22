<template>
  <teleport to="body">
    <div v-if="show && invoice" class="modal-backdrop" @click.self="$emit('close')">
      <div class="modal-card modal-card--xl animate-fade-in" role="dialog" aria-modal="true">
      <!-- Modal Top Controls (Hidden on print) -->
      <div class="modal-header no-print">
        <div class="header-left">
          <div class="modal-icon-badge" :class="invoice.status === 'vigente' ? 'modal-icon-badge--green' : 'modal-icon-badge--red'">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;display:block">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
          </div>
          <div>
            <div class="header-title-row">
              <h3 class="modal-title font-mono">{{ invoice.series }}-{{ String(invoice.folio).padStart(4, '0') }}</h3>
              <span class="status-pill" :class="invoice.status === 'vigente' ? 'status-pill--green' : 'status-pill--red'">
                {{ invoice.status === 'vigente' ? 'CFDI Vigente' : 'CFDI Cancelada' }}
              </span>
            </div>
            <p class="modal-subtitle font-mono">UUID: {{ invoice.uuid }}</p>
          </div>
        </div>

        <div class="header-actions">
          <button class="btn-action" title="Copiar Folio Fiscal UUID" @click="copyUuid">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline-block">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
            <span>{{ copied ? '¡Copiado!' : 'Copiar UUID' }}</span>
          </button>
          <button class="btn-action" title="Descargar archivo oficial XML" @click="downloadXml">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline-block">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span>Descargar XML</span>
          </button>
          <button class="btn-action btn-action--primary" title="Imprimir o Guardar en PDF" @click="printInvoice">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline-block">
              <polyline points="6 9 6 2 18 2 18 9"/>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
              <rect x="6" y="14" width="12" height="8"/>
            </svg>
            <span>Imprimir / PDF</span>
          </button>
          <button v-if="invoice.status === 'vigente'" class="btn-action btn-action--danger" title="Cancelar comprobante ante el SAT" @click="promptCancel">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;display:inline-block">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <span>Cancelar</span>
          </button>
          <button class="btn-icon close-btn" title="Cerrar ventana" @click="$emit('close')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:16px;height:16px;display:block">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Printable SAT CFDI 4.0 Layout Sheet -->
      <div id="sat-invoice-print-area" class="printable-sheet">
        <!-- Watermark for Cancelled -->
        <div v-if="invoice.status === 'cancelada'" class="cancelled-watermark">
          CANCELADA
        </div>

        <!-- Sheet Header: Workshop & Fiscal Certification -->
        <div class="sheet-header">
          <div class="sheet-emisor">
            <div class="emisor-brand">
              <svg viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;min-width:28px;display:block;color:#2563eb">
                <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
              </svg>
              <div>
                <h2 class="emisor-title">TALLER MECÁNICO AUTOMOTRIZ ESPECIALIZADO</h2>
                <span class="emisor-sub">S.A. DE C.V.</span>
              </div>
            </div>
            <div class="emisor-details">
              <p><strong>RFC:</strong> TME200101ABC</p>
              <p><strong>Régimen Fiscal:</strong> 601 - General de Ley Personas Morales</p>
              <p><strong>Lugar de Expedición:</strong> C.P. 06000 (Ciudad de México)</p>
              <p><strong>Tipo de Comprobante:</strong> I - Ingreso | <strong>Exportación:</strong> 01 - No aplica</p>
            </div>
          </div>

          <div class="sheet-fiscal-box">
            <div class="fiscal-box-header">FACTURA ELECTRÓNICA (CFDI 4.0)</div>
            <div class="fiscal-box-body">
              <div class="fiscal-row">
                <span class="fiscal-k">SERIE / FOLIO:</span>
                <span class="fiscal-v font-bold" style="color:#2563eb">{{ invoice.series }}-{{ String(invoice.folio).padStart(4, '0') }}</span>
              </div>
              <div class="fiscal-row">
                <span class="fiscal-k">FOLIO FISCAL (UUID):</span>
                <span class="fiscal-v font-mono" style="font-size:0.62rem;word-break:break-all">{{ invoice.uuid }}</span>
              </div>
              <div class="fiscal-row">
                <span class="fiscal-k">FECHA DE EMISIÓN:</span>
                <span class="fiscal-v">{{ formatDate(invoice.fecha_emision) }}</span>
              </div>
              <div class="fiscal-row">
                <span class="fiscal-k">FECHA DE CERTIFICACIÓN:</span>
                <span class="fiscal-v">{{ formatDate(invoice.fecha_timbrado) }}</span>
              </div>
              <div class="fiscal-row">
                <span class="fiscal-k">NO. CERTIFICADO SAT:</span>
                <span class="fiscal-v font-mono">{{ invoice.no_certificado_sat }}</span>
              </div>
              <div class="fiscal-row">
                <span class="fiscal-k">NO. CERTIFICADO EMISOR:</span>
                <span class="fiscal-v font-mono">{{ invoice.no_certificado_emisor }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Receptor Box -->
        <div class="sheet-receptor-box">
          <div class="receptor-title">DATOS DEL RECEPTOR (CLIENTE)</div>
          <div class="receptor-grid">
            <div class="receptor-cell">
              <span class="receptor-k">RFC:</span>
              <span class="receptor-v font-mono font-bold">{{ invoice.rfc_receptor }}</span>
            </div>
            <div class="receptor-cell">
              <span class="receptor-k">Razón Social:</span>
              <span class="receptor-v font-bold uppercase">{{ invoice.razon_social_receptor }}</span>
            </div>
            <div class="receptor-cell">
              <span class="receptor-k">Domicilio Fiscal (C.P.):</span>
              <span class="receptor-v font-mono">{{ invoice.codigo_postal_receptor }}</span>
            </div>
            <div class="receptor-cell">
              <span class="receptor-k">Régimen Fiscal:</span>
              <span class="receptor-v">{{ invoice.regimen_fiscal_receptor }}</span>
            </div>
            <div class="receptor-cell">
              <span class="receptor-k">Uso del CFDI:</span>
              <span class="receptor-v">{{ invoice.uso_cfdi }}</span>
            </div>
            <div class="receptor-cell">
              <span class="receptor-k">Forma / Método de Pago:</span>
              <span class="receptor-v">{{ invoice.forma_pago }} | {{ invoice.metodo_pago }} (Pago en una sola exhibición)</span>
            </div>
            <div v-if="invoice.order_vehicle" class="receptor-cell receptor-cell--full">
              <span class="receptor-k">Vehículo y Orden de Trabajo:</span>
              <span class="receptor-v">Orden #{{ invoice.order_id }} — {{ invoice.order_vehicle }}</span>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <div class="sheet-table-wrap">
          <table class="sheet-table">
            <thead>
              <tr>
                <th style="width: 95px;">Clave SAT</th>
                <th style="width: 60px; text-align: center;">Cant.</th>
                <th style="width: 75px; text-align: center;">Unidad</th>
                <th>Descripción del Servicio o Refacción</th>
                <th style="width: 100px; text-align: right;">P. Unitario</th>
                <th style="width: 85px; text-align: right;">IVA (16%)</th>
                <th style="width: 110px; text-align: right;">Importe</th>
              </tr>
            </thead>
            <tbody>
              <!-- Render from order items if available, or fallback summary item -->
              <template v-if="invoice.order && invoice.order.items && invoice.order.items.length">
                <tr v-for="item in invoice.order.items" :key="item.id">
                  <td class="font-mono" style="font-size:0.7rem;color:#64748b">
                    {{ isLabor(item) ? '78181500' : '25171700' }}
                  </td>
                  <td style="text-align:center" class="font-mono">{{ item.quantity }}</td>
                  <td style="text-align:center;font-size:0.7rem;color:#475569">
                    {{ isLabor(item) ? 'E48 Serv.' : 'H87 Pza.' }}
                  </td>
                  <td>
                    <div style="font-weight:600;color:#0f172a">{{ item.product ? item.product.name : 'Mantenimiento Mecánico' }}</div>
                    <div style="font-size:0.65rem;color:#64748b">Impuesto: 002 IVA Tasa 0.160000 | ObjetoImp: 02</div>
                  </td>
                  <td style="text-align:right" class="font-mono">{{ formatMoney(getItemUnitPriceWithoutIva(item)) }}</td>
                  <td style="text-align:right;color:#475569" class="font-mono">{{ formatMoney(getItemIva(item)) }}</td>
                  <td style="text-align:right;font-weight:700;color:#0f172a" class="font-mono">{{ formatMoney(getItemSubtotalWithoutIva(item)) }}</td>
                </tr>
              </template>
              <tr v-else>
                <td class="font-mono" style="font-size:0.7rem;color:#64748b">78181500</td>
                <td style="text-align:center" class="font-mono">1</td>
                <td style="text-align:center;font-size:0.7rem;color:#475569">E48 Serv.</td>
                <td>
                  <div style="font-weight:600;color:#0f172a">Servicio de mantenimiento preventivo y correctivo automotriz</div>
                  <div style="font-size:0.65rem;color:#64748b">Impuesto: 002 IVA Tasa 0.160000 | ObjetoImp: 02</div>
                </td>
                <td style="text-align:right" class="font-mono">{{ formatMoney(invoice.subtotal) }}</td>
                <td style="text-align:right;color:#475569" class="font-mono">{{ formatMoney(invoice.iva_trasladado) }}</td>
                <td style="text-align:right;font-weight:700;color:#0f172a" class="font-mono">{{ formatMoney(invoice.subtotal) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals and Amount in Words -->
        <div class="sheet-totals-section">
          <div class="sheet-amount-words">
            <span class="amount-words-label">CANTIDAD CON LETRA:</span>
            <div class="amount-words-text">
              {{ numberToWords(invoice.total) }} PESOS {{ getCents(invoice.total) }}/100 M.N.
            </div>
            <div class="amount-words-currency">
              Moneda: MXN - Peso Mexicano | Tipo de Cambio: 1.0000
            </div>
          </div>

          <div class="sheet-totals-box">
            <div class="totals-row">
              <span>SUBTOTAL:</span>
              <span class="font-mono">{{ formatMoney(invoice.subtotal) }}</span>
            </div>
            <div class="totals-row">
              <span>IVA TRASLADADO (16%):</span>
              <span class="font-mono">+ {{ formatMoney(invoice.iva_trasladado) }}</span>
            </div>
            <div class="totals-row totals-row--final">
              <span>TOTAL (MXN):</span>
              <span class="font-mono final-total-amount">{{ formatMoney(invoice.total) }}</span>
            </div>
          </div>
        </div>

        <!-- SAT Certification Seals and 2D QR Code -->
        <div class="sheet-seals-section">
          <div class="qr-col">
            <canvas ref="qrCanvas" class="qr-canvas"></canvas>
            <span class="qr-label">Verificación SAT</span>
          </div>

          <div class="seals-col">
            <div class="seal-block">
              <span class="seal-title">SELLO DIGITAL DEL EMISOR (CFDI):</span>
              <div class="seal-text">{{ invoice.sello_emisor }}</div>
            </div>

            <div class="seal-block">
              <span class="seal-title">SELLO DIGITAL DEL SAT:</span>
              <div class="seal-text">{{ invoice.sello_sat }}</div>
            </div>

            <div class="seal-block">
              <span class="seal-title">CADENA ORIGINAL DEL COMPLEMENTO DE CERTIFICACIÓN DIGITAL DEL SAT:</span>
              <div class="seal-text font-mono">{{ invoice.cadena_original }}</div>
            </div>

            <div class="sat-footer-legend">
              ESTE DOCUMENTO ES UNA REPRESENTACIÓN IMPRESA DE UN CFDI VERSIÓN 4.0
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import QRCode from 'qrcode'
import { formatMoney } from '../../utils/format'

const props = defineProps({
  show: { type: Boolean, default: false },
  invoice: { type: Object, default: null },
})

const emit = defineEmits(['close', 'cancelled'])

const qrCanvas = ref(null)
const copied = ref(false)

function isLabor(item) {
  if (item.product && typeof item.product.is_service === 'boolean') {
    return item.product.is_service
  }
  const name = item.product ? item.product.name.toLowerCase() : ''
  return name.includes('mano') || name.includes('servicio') || name.includes('diagnostico') || name.includes('protocolo') || name.includes('mantenimiento') || name.includes('purga') || name.includes('alineacion') || name.includes('cambio y purgado')
}

function getItemSubtotalWithoutIva(item) {
  const net = (item.unit_price || 0) * (item.quantity || 1)
  return Math.round((net / 1.16) * 100) / 100
}

function getItemIva(item) {
  const net = (item.unit_price || 0) * (item.quantity || 1)
  const sub = getItemSubtotalWithoutIva(item)
  return Math.round((net - sub) * 100) / 100
}

function getItemUnitPriceWithoutIva(item) {
  const sub = getItemSubtotalWithoutIva(item)
  const qty = item.quantity || 1
  return Math.round((sub / qty) * 100) / 100
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleString('es-MX', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
}

function renderQr() {
  if (!qrCanvas.value || !props.invoice) return
  const url = props.invoice.qr_code_url || `https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=${props.invoice.uuid}`
  QRCode.toCanvas(qrCanvas.value, url, {
    width: 110,
    margin: 1,
    color: { dark: '#000000', light: '#ffffff' },
  }, (err) => {
    if (err) console.error('Error generando QR SAT:', err)
  })
}

watch(() => props.show, async (newVal) => {
  if (newVal) {
    await nextTick()
    renderQr()
  }
})

watch(() => props.invoice, async () => {
  if (props.show) {
    await nextTick()
    renderQr()
  }
})

function copyUuid() {
  if (!props.invoice) return
  navigator.clipboard.writeText(props.invoice.uuid)
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

function downloadXml() {
  if (!props.invoice) return
  const url = `${import.meta.env.VITE_API_URL}/invoices/${props.invoice.id}/xml`
  window.open(url, '_blank')
}

function printInvoice() {
  document.body.classList.add('is-printing-invoice')
  setTimeout(() => {
    window.print()
  }, 60)
}

function onBeforePrint() {
  if (props.show) {
    document.body.classList.add('is-printing-invoice')
  }
}

function onAfterPrint() {
  document.body.classList.remove('is-printing-invoice')
}

onMounted(() => {
  window.addEventListener('beforeprint', onBeforePrint)
  window.addEventListener('afterprint', onAfterPrint)
})

onUnmounted(() => {
  window.removeEventListener('beforeprint', onBeforePrint)
  window.removeEventListener('afterprint', onAfterPrint)
  document.body.classList.remove('is-printing-invoice')
})

watch(() => props.show, (newVal) => {
  if (!newVal) {
    document.body.classList.remove('is-printing-invoice')
  }
})

function promptCancel() {
  emit('cancelled', props.invoice)
}

function getCents(amount) {
  const cents = Math.round((amount - Math.floor(amount)) * 100)
  return String(cents).padStart(2, '0')
}

function numberToWords(num) {
  const integer = Math.floor(num)
  if (integer === 0) return 'CERO'
  
  const units = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE']
  const tens = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA']
  const teens = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE']
  const hundreds = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS']

  function convertGroup(n) {
    let output = ''
    if (n === 100) return 'CIEN'
    if (n >= 100) {
      output += hundreds[Math.floor(n / 100)] + ' '
      n %= 100
    }
    if (n >= 10 && n <= 19) {
      output += teens[n - 10]
      return output.trim()
    }
    if (n >= 20) {
      const ten = Math.floor(n / 10)
      const unit = n % 10
      if (ten === 2 && unit > 0) {
        output += 'VEINTI' + units[unit]
        return output.trim()
      }
      output += tens[ten]
      if (unit > 0) output += ' Y ' + units[unit]
      return output.trim()
    }
    if (n > 0) {
      output += units[n]
    }
    return output.trim()
  }

  if (integer >= 1000000) {
    const millions = Math.floor(integer / 1000000)
    const remainder = integer % 1000000
    const mStr = millions === 1 ? 'UN MILLÓN' : convertGroup(millions) + ' MILLONES'
    return mStr + (remainder > 0 ? ' ' + numberToWords(remainder) : '')
  }

  if (integer >= 1000) {
    const thousands = Math.floor(integer / 1000)
    const remainder = integer % 1000
    const tStr = thousands === 1 ? 'MIL' : convertGroup(thousands) + ' MIL'
    return tStr + (remainder > 0 ? ' ' + convertGroup(remainder) : '')
  }

  return convertGroup(integer)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(4, 9, 20, 0.82);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal-card--xl {
  background: #0f172a;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 16px;
  width: 100%;
  max-width: 960px;
  max-height: 94vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
  overflow: hidden;
}

.modal-header {
  padding: 14px 20px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #131d35;
  gap: 16px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-icon-badge {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-icon-badge--green {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.modal-icon-badge--red {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0;
}

.modal-subtitle {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 2px;
}

.status-pill {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
}

.status-pill--green {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
}

.status-pill--red {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  border-radius: 8px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(30, 41, 59, 0.6);
  color: #cbd5e1;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: rgba(30, 41, 59, 0.95);
  color: #fff;
}

.btn-action--primary {
  background: #2563eb;
  border-color: #3b82f6;
  color: #fff;
  font-weight: 600;
}

.btn-action--primary:hover {
  background: #1d4ed8;
}

.btn-action--danger {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
  color: #f87171;
}

.btn-action--danger:hover {
  background: rgba(239, 68, 68, 0.3);
  color: #fff;
}

.close-btn {
  margin-left: 4px;
}

/* ========================================================================= */
/* Printable Sheet Design (Classic High-Fidelity Mexican SAT CFDI 4.0 Format) */
/* ========================================================================= */
.printable-sheet {
  background: #ffffff;
  color: #1e293b;
  padding: 28px 32px;
  overflow-y: auto;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  position: relative;
}

.cancelled-watermark {
  position: absolute;
  top: 40%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-30deg);
  font-size: 5rem;
  font-weight: 900;
  color: rgba(239, 68, 68, 0.18);
  border: 8px solid rgba(239, 68, 68, 0.18);
  padding: 10px 40px;
  border-radius: 16px;
  pointer-events: none;
  z-index: 10;
}

.sheet-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 16px;
  margin-bottom: 14px;
}

.sheet-emisor {
  flex: 1;
}

.emisor-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.emisor-title {
  font-size: 0.95rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  line-height: 1.2;
}

.emisor-sub {
  font-size: 0.75rem;
  font-weight: 700;
  color: #475569;
}

.emisor-details {
  font-size: 0.75rem;
  color: #475569;
  line-height: 1.45;
}

.sheet-fiscal-box {
  width: 320px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  overflow: hidden;
}

.fiscal-box-header {
  background: #0f172a;
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 5px 8px;
  text-align: center;
  letter-spacing: 0.05em;
}

.fiscal-box-body {
  padding: 8px;
  font-size: 0.72rem;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.fiscal-row {
  display: flex;
  justify-content: space-between;
}

.fiscal-k {
  color: #64748b;
  font-weight: 600;
  font-size: 0.68rem;
}

.fiscal-v {
  color: #0f172a;
  text-align: right;
}

.sheet-receptor-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 16px;
}

.receptor-title {
  font-size: 0.72rem;
  font-weight: 800;
  color: #1e3a8a;
  margin-bottom: 8px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 4px;
  letter-spacing: 0.04em;
}

.receptor-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px 16px;
  font-size: 0.78rem;
}

.receptor-cell {
  display: flex;
  flex-direction: column;
}

.receptor-cell--full {
  grid-column: 1 / -1;
  padding-top: 6px;
  border-top: 1px solid #e2e8f0;
  flex-direction: row;
  gap: 6px;
  align-items: center;
}

.receptor-k {
  color: #64748b;
  font-weight: 600;
  font-size: 0.68rem;
  margin-bottom: 1px;
}

.receptor-v {
  color: #0f172a;
  line-height: 1.3;
}

.sheet-table-wrap {
  margin-bottom: 16px;
}

.sheet-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.75rem;
}

.sheet-table th {
  background: #0f172a;
  color: #ffffff;
  padding: 6px 8px;
  font-weight: 600;
  font-size: 0.7rem;
  text-align: left;
}

.sheet-table td {
  padding: 7px 8px;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}

.sheet-totals-section {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 18px;
  border-top: 1px solid #cbd5e1;
  padding-top: 12px;
}

.sheet-amount-words {
  flex: 1;
}

.amount-words-label {
  font-size: 0.7rem;
  color: #64748b;
  display: block;
  font-weight: 600;
  margin-bottom: 2px;
}

.amount-words-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: #0f172a;
  text-transform: uppercase;
}

.amount-words-currency {
  font-size: 0.68rem;
  color: #64748b;
  margin-top: 6px;
}

.sheet-totals-box {
  width: 280px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
}

.totals-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: #475569;
  margin-bottom: 4px;
}

.totals-row--final {
  border-top: 2px solid #0f172a;
  padding-top: 6px;
  margin-top: 6px;
  color: #0f172a;
  font-weight: 700;
}

.final-total-amount {
  font-weight: 900;
  color: #2563eb;
  font-size: 1.05rem;
}

.sheet-seals-section {
  display: flex;
  gap: 16px;
  border-top: 1px solid #e2e8f0;
  padding-top: 14px;
}

.qr-col {
  width: 110px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.qr-canvas {
  width: 110px !important;
  height: 110px !important;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
}

.qr-label {
  font-size: 0.62rem;
  color: #64748b;
  text-align: center;
  margin-top: 4px;
}

.seals-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.seal-block {
  display: flex;
  flex-direction: column;
}

.seal-title {
  font-size: 0.62rem;
  font-weight: 700;
  color: #475569;
}

.seal-text {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.58rem;
  color: #64748b;
  word-break: break-all;
  line-height: 1.15;
}

.sat-footer-legend {
  font-size: 0.65rem;
  font-weight: 800;
  color: #0f172a;
  text-align: center;
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px solid #cbd5e1;
  letter-spacing: 0.05em;
}

/* ========================================================================= */
/* Print Specific Styles */
/* ========================================================================= */
@media print {
  @page {
    size: letter portrait;
    margin: 8mm;
  }

  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  .modal-backdrop {
    position: static !important;
    inset: auto !important;
    background: transparent !important;
    backdrop-filter: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
    width: 100% !important;
    min-height: auto !important;
    box-shadow: none !important;
  }

  .modal-card--xl {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    max-width: 100% !important;
    max-height: none !important;
    height: auto !important;
    overflow: visible !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .no-print {
    display: none !important;
  }

  .printable-sheet {
    position: static !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-shadow: none !important;
    border: none !important;
    overflow: visible !important;
    background: #ffffff !important;
    color: #1e293b !important;
  }
}
</style>
