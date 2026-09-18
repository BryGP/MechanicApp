<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">
    <!-- Brand -->
    <div class="brand">
      <span class="brand-icon">&#9881;</span>
      <span class="brand-name">AutoServicio</span>
    </div>

    <!-- Nav -->
    <nav class="nav">
      <router-link to="/" class="nav-item" exact-active-class="nav-item--active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
        <span class="nav-label">Dashboard</span>
      </router-link>
      <router-link to="/productos" class="nav-item" active-class="nav-item--active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.6 1 12 1S6 2.54 6 4.66C6 5.12 6.11 5.56 6.18 6H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-8-3c1.99 0 4 .96 4 1.66C16 5.48 14.07 6 12 6S8 5.48 8 4.66C8 3.96 10.01 3 12 3zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
        <span class="nav-label">Productos</span>
      </router-link>
      <router-link to="/ordenes" class="nav-item" active-class="nav-item--active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        <span class="nav-label">Ordenes</span>
      </router-link>
      <router-link to="/contabilidad" class="nav-item" active-class="nav-item--active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        <span class="nav-label">Contabilidad</span>
      </router-link>
      <router-link to="/reportes" class="nav-item" active-class="nav-item--active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        <span class="nav-label">Reportes & SQL</span>
      </router-link>
    </nav>

    <!-- Toggle button -->
    <button class="toggle-btn" @click="$emit('toggle')" :title="collapsed ? 'Expandir' : 'Colapsar'">
      <svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px" :style="{ transform: collapsed ? 'rotate(180deg)' : '' }">
        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
      </svg>
    </button>
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
  transition: width var(--transition);
  overflow: hidden;
}
.sidebar--collapsed { width: var(--sidebar-sm); }

.brand {
  display: flex; align-items: center; gap: 12px;
  padding: 20px 18px 16px;
  border-bottom: 1px solid var(--border);
  min-height: var(--header-h);
}
.brand-icon {
  font-size: 1.4rem; color: var(--accent);
  min-width: 32px; text-align: center; line-height: 1;
}
.brand-name {
  font-size: 1rem; font-weight: 800; color: var(--text);
  white-space: nowrap; letter-spacing: -0.02em;
}

.nav { flex: 1; padding: 12px 10px; display: flex; flex-direction: column; gap: 4px; }

.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 10px; border-radius: var(--radius-sm);
  color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500;
  transition: all var(--transition); white-space: nowrap;
}
.nav-item:hover { background: var(--bg-hover); color: var(--text); }
.nav-item--active { background: var(--accent-glow); color: var(--accent); }
.nav-item--active:hover { background: var(--accent-glow); }

.nav-icon { width: 20px; height: 20px; min-width: 20px; }

.toggle-btn {
  margin: 12px 10px; padding: 9px;
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: var(--radius-sm); cursor: pointer;
  color: var(--text-muted); display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.toggle-btn:hover { color: var(--text); }
.toggle-btn svg { transition: transform var(--transition); }

.sidebar--collapsed .brand-name,
.sidebar--collapsed .nav-label { display: none; }
.sidebar--collapsed .nav-item { justify-content: center; padding: 10px; }
.sidebar--collapsed .brand { justify-content: center; padding: 20px 10px 16px; }
</style>