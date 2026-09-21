<template>
  <div class="shell">
    <Sidebar :collapsed="collapsed" @toggle="collapsed = !collapsed" />
    <div class="main" :class="{ 'main--sm': collapsed }">
      <AppHeader />
      <main class="content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
    <Toast />
    <ConfirmModal />
  </div>
</template>

<script setup>
/**
 * @fileoverview Main Application Layout Shell Component
 * @module App
 * @description Provides the master application shell layout, orchestrating the responsive
 * collapsible sidebar, top navigation header, router view container, toast stack,
 * and centralized confirmation dialogs.
 */

import { ref } from 'vue'
import Sidebar from './components/layout/Sidebar.vue'
import AppHeader from './components/layout/AppHeader.vue'
import Toast from './components/ui/Toast.vue'
import ConfirmModal from './components/ui/ConfirmModal.vue'

/** Reactive collapsed state for the navigation sidebar */
const collapsed = ref(false)
</script>

<style scoped>
.shell { display: flex; height: 100vh; overflow: hidden; }
.main {
  flex: 1; display: flex; flex-direction: column; overflow: hidden;
  margin-left: var(--sidebar-w);
  transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}
.main--sm { margin-left: var(--sidebar-sm); }
.content { flex: 1; overflow-y: auto; padding: 2rem; }
</style>