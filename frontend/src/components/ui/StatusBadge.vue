<template>
  <span :class="['badge', cls[status]]">{{ labels[status] ?? status }}</span>
</template>
<script setup>
defineProps({ status: String })
const labels = { open: 'Abierta', in_progress: 'En proceso', done: 'Terminada', delivered: 'Entregada' }
const cls    = { open: 'b-blue', in_progress: 'b-amber', done: 'b-green', delivered: 'b-gray' }
</script>
<style scoped>
.badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}
.badge::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}
.b-blue  { background: #0b1e38; color: #60a5fa; border: 1px solid #2563eb; }
.b-blue::before { background: #38bdf8; box-shadow: 0 0 6px #38bdf8; }

.b-amber { background: #2e1a05; color: #fbbf24; border: 1px solid #d97706; }
.b-amber::before { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; }

.b-green { background: #062419; color: #34d399; border: 1px solid #059669; }
.b-green::before { background: #10b981; box-shadow: 0 0 6px #10b981; }

.b-gray  { background: #131b2c; color: #cbd5e1; border: 1px solid #334155; }
.b-gray::before { background: #94a3b8; }
</style>