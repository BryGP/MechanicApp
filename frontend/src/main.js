/**
 * main.js — Application Entry Point
 *
 * Bootstraps the Vue 3 application by:
 *   1. Creating the root Vue app instance from App.vue
 *   2. Registering Pinia as the global state management store
 *   3. Registering Vue Router for client-side navigation
 *   4. Mounting the app to the #app div in index.html
 *
 * Environment variables (defined in .env):
 *   VITE_API_URL — Base URL for all backend API calls (e.g. http://localhost:8000/api)
 */

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

const app = createApp(App);

// Pinia: centralized reactive state store (products, orders)
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.mount('#app');

// Debug: confirm the API base URL is loaded from .env
console.log('[MechanicApp] API URL:', import.meta.env.VITE_API_URL);
