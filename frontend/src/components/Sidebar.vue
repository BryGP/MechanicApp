<!--
  Sidebar.vue
  ============
  Persistent left navigation panel rendered on every page via App.vue.
  Contains the app title and navigation links to the two main sections:
    - Productos (/): inventory management
    - Órdenes (/orders): service order management

  Uses Vue Router's <router-link> with active-class to highlight
  the currently active route automatically.
-->
<template>
  <aside class="sidebar">
    <h1 class="title">Autoservicio</h1>
    <nav>
      <router-link to="/" class="link" active-class="active">Productos</router-link>
      <router-link to="/orders" class="link" active-class="active">Órdenes</router-link>
    </nav>
  </aside>
</template>

<script setup></script>

<style scoped>
.sidebar {
  width: 200px;
  background-color: #2196f3;
  color: #fff;
  display: flex;
  flex-direction: column;
  padding: 1rem;
}

.title {
  margin-bottom: 1.5rem;
  font-size: 1.2rem;
  font-weight: bold;
}

.link {
  display: block;
  margin-bottom: 0.5rem;
  color: inherit;
  text-decoration: none;
  padding: 0.5rem;
  border-radius: 4px;
}

.link:hover {
  background-color: #1976d2;
}

.active {
  background-color: #1976d2;
}
</style>
