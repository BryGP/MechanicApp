<template>
  <div id="app-layout">
    <Sidebar />
    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import Sidebar from './components/Sidebar.vue';
</script>

<style scoped>
#app-layout {
  display: flex;
  height: 100vh;
  font-family: Arial, sans-serif;
}

.content {
  flex: 1;
  background-color: #f5f5f5;
  padding: 1rem;
}
</style>