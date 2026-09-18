<template>
  <div class="stat-card">
    <div class="stat-icon" :style="{ background: `${color}18`, color }">{{ icon }}</div>
    <div class="stat-info">
      <div class="stat-value">{{ value }}</div>
      <div class="stat-label">{{ label }}</div>
    </div>
  </div>
</template>
<script setup>
defineProps({ icon: String, label: String, value: [String, Number], color: { type: String, default: 'var(--accent)' } })
</script>
<style scoped>
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 1.25rem 1.5rem;
  display: flex; align-items: center; gap: 1rem;
  transition: transform var(--transition), box-shadow var(--transition);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
.stat-icon {
  width: 52px; height: 52px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; flex-shrink: 0;
}
.stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; }
.stat-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; font-weight: 500; }
</style>