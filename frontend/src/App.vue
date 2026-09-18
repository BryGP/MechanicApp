<template>
  <div class="shell">
    <Sidebar :collapsed="collapsed" @toggle="collapsed = !collapsed" />
    <div class="main" :class="{ 'main--sm': collapsed }">
      <AppHeader @toggle="collapsed = !collapsed" />
      <main class="content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
    <Toast />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from './components/layout/Sidebar.vue'
import AppHeader from './components/layout/AppHeader.vue'
import Toast from './components/ui/Toast.vue'
const collapsed = ref(false)
</script>

<style scoped>
.shell { display: flex; height: 100vh; overflow: hidden; }
.main {
  flex: 1; display: flex; flex-direction: column; overflow: hidden;
  margin-left: var(--sidebar-w);
  transition: margin-left var(--transition);
}
.main--sm { margin-left: var(--sidebar-sm); }
.content { flex: 1; overflow-y: auto; padding: 2rem; }
</style>