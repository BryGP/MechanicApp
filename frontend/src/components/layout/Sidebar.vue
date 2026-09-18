<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">
    <!-- Brand -->
    <div class="brand">
      <div class="brand-inner">
        <svg class="brand-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
        </svg>
        <span class="brand-name">AutoServicio</span>
      </div>
    </div>

    <!-- Nav -->
    <nav class="nav">
      <router-link to="/" class="nav-item" exact-active-class="nav-item--active" :title="collapsed ? 'Dashboard' : ''">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
        <span class="nav-label">Dashboard</span>
      </router-link>
      <router-link to="/productos" class="nav-item" active-class="nav-item--active" :title="collapsed ? 'Inventario' : ''">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.6 1 12 1S6 2.54 6 4.66C6 5.12 6.11 5.56 6.18 6H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-8-3c1.99 0 4 .96 4 1.66C16 5.48 14.07 6 12 6S8 5.48 8 4.66C8 3.96 10.01 3 12 3zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
        <span class="nav-label">Productos</span>
      </router-link>
      <router-link to="/ordenes" class="nav-item" active-class="nav-item--active" :title="collapsed ? 'Órdenes' : ''">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        <span class="nav-label">Órdenes</span>
      </router-link>
      <router-link to="/contabilidad" class="nav-item" active-class="nav-item--active" :title="collapsed ? 'Contabilidad' : ''">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        <span class="nav-label">Contabilidad</span>
      </router-link>
      <router-link to="/reportes" class="nav-item" active-class="nav-item--active" :title="collapsed ? 'Reportes' : ''">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        <span class="nav-label">Reportes & SQL</span>
      </router-link>
    </nav>

    <!-- Toggle button: único control de la barra lateral -->
    <div class="toggle-wrap">
      <button class="toggle-btn" @click="$emit('toggle')" :title="collapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="chevron-ic" :class="{ 'chevron--rotated': collapsed }">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        <span class="toggle-label" v-if="!collapsed">Colapsar menú</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
defineProps({ collapsed: Boolean })
defineEmits(['toggle'])
</script>

<style scoped>
.sidebar {
  position: fixed; top: 0; left: 0; height: 100vh; z-index: 50;
  width: var(--sidebar-w);
  background: var(--bg-sidebar);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
}
.sidebar--collapsed { width: var(--sidebar-sm); }

.brand {
  height: var(--header-h); min-height: var(--header-h);
  display: flex; align-items: center;
  padding: 0 16px;
  border-bottom: 1px solid var(--border);
  overflow: hidden;
}
.brand-inner {
  display: flex; align-items: center; gap: 12px;
  width: 100%;
}
.brand-icon {
  width: 24px; height: 24px; min-width: 24px;
  color: var(--accent);
  filter: drop-shadow(0 0 8px var(--accent-glow));
}
.brand-name {
  font-size: 1.05rem; font-weight: 800; color: var(--text);
  white-space: nowrap; letter-spacing: -0.02em;
}

.nav {
  flex: 1; padding: 14px 10px;
  display: flex; flex-direction: column; gap: 4px;
}

.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: var(--radius-sm);
  color: var(--text-muted); text-decoration: none;
  font-size: 0.875rem; font-weight: 500;
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s ease, color 0.2s ease;
  white-space: nowrap;
  position: relative;
}
.nav-item:hover {
  background: var(--bg-hover);
  color: var(--text);
  transform: translateX(3px);
}
.nav-item:active {
  transform: scale(0.98);
}
.nav-item--active {
  background: rgba(37, 99, 235, 0.16);
  color: #60a5fa;
  font-weight: 600;
}
.nav-item--active::before {
  content: '';
  position: absolute;
  left: 0; top: 18%; bottom: 18%;
  width: 3px;
  border-radius: 0 4px 4px 0;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}
.nav-item--active:hover {
  background: rgba(37, 99, 235, 0.22);
}

.nav-icon {
  width: 20px; height: 20px; min-width: 20px;
  transition: transform 0.2s ease;
}
.nav-item:hover .nav-icon {
  transform: scale(1.08);
}

.nav-label {
  white-space: nowrap;
}

.toggle-wrap {
  padding: 12px 10px;
  border-top: 1px solid var(--border);
}
.toggle-btn {
  width: 100%;
  padding: 9px 12px;
  background: rgba(24, 37, 68, 0.35);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  color: var(--text-muted);
  display: flex; align-items: center; gap: 10px;
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
  font-family: inherit;
  white-space: nowrap;
}
.toggle-btn:hover {
  background: var(--bg-hover);
  color: var(--text);
  border-color: rgba(148, 163, 184, 0.25);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
.toggle-btn:active {
  transform: scale(0.97);
}
.chevron-ic {
  width: 18px; height: 18px; min-width: 18px;
  transition: transform 0.35s cubic-bezier(0.34, 1.5, 0.64, 1);
}
.chevron--rotated {
  transform: rotate(180deg);
}
.toggle-label {
  font-size: 0.8rem; font-weight: 500; white-space: nowrap;
}

/* Estado colapsado */
.sidebar--collapsed .brand-inner {
  justify-content: center;
}
.sidebar--collapsed .brand-name {
  display: none;
}
.sidebar--collapsed .nav-item {
  justify-content: center;
  padding: 11px;
}
.sidebar--collapsed .nav-item:hover {
  transform: scale(1.06);
}
.sidebar--collapsed .nav-label {
  display: none;
}
.sidebar--collapsed .toggle-btn {
  justify-content: center;
  padding: 9px 0;
}
</style>