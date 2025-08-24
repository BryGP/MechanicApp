import { defineStore } from 'pinia';

// frontend/src/store/index.js
const base = import.meta.env.VITE_API_URL;

// Administracion del taller para mecanicos
export const useStore = defineStore('main', {
  state: () => ({products: [], orders: [] }),
  actions: {
    // Obtencion de productos y ordenes
    async fetchProducts() {
      const res = await fetch(`${base}/products`);
      this.products = await res.json();
    },
    async fetchOrders() {
      const res = await fetch(`${base}/orders`);
      this.orders = await res.json();
    },

    // Creacion de ordenes
    async createOrder(payload) {
      // payload: { customer_name, vehicle, items:[{product_id, qty}] }
      const res = await fetch(`${base}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      // Si falla la creacion de la orden
      if (!res.ok) throw new Error('Error creando la orden');
      const created = await res.json();
      
      // Actualizar lista
      await this.fetchOrders();
      return created;
    },
  },
});