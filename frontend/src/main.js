/**
 * @fileoverview Main Application Entry Point
 * @module main
 * @description Bootstraps the Vue 3 single-page application, registering the Pinia
 * global state store, Vue Router, base stylesheet tokens, and mounting the root component.
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/styles/main.css'

/** Root Vue application instance */
const app = createApp(App)

// Register core plugins
app.use(createPinia())
app.use(router)

// Mount to DOM root
app.mount('#app')