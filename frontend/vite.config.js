/**
 * @fileoverview Vite Build Configuration
 * @module vite.config
 * @description Configures the Vite development server, Single File Component (SFC)
 * compilation via @vitejs/plugin-vue, and custom localhost port mapping.
 */

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    host: true,
  },
})