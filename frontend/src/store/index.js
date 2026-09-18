import { defineStore } from 'pinia';

/**
 * Main Pinia Store — useStore
 *
 * Central state manager for the MechanicApp frontend.
 * Holds and manages two core data collections:
 *   - products: workshop inventory items fetched from the API
 *   - orders:   service orders with their associated line items
 *
 * All API calls use the VITE_API_URL env variable as the base URL,
 * which points to the Laravel backend (default: http://localhost:8000/api).
 *
 * Usage in any component:
 *   import { useStore } from '../store';
 *   const store = useStore();
 */
const base = import.meta.env.VITE_API_URL;

export const useStore = defineStore('main', {
  /**
   * Reactive state — shared across all components that import this store.
   */
  state: () => ({
    /** @type {Array} List of all products from GET /api/products */
    products: [],
    /** @type {Array} List of all orders (with items) from GET /api/orders */
    orders: [],
  }),

  actions: {
    /**
     * Fetch all inventory products from the API and update local state.
     * Called on component mount by ProductsView and Dashboard.
     */
    async fetchProducts() {
      const res = await fetch(`${base}/products`);
      this.products = await res.json();
    },

    /**
     * Fetch all service orders (with their line items) from the API.
     * Called on component mount by OrdersView, OrdersList, and Dashboard.
     */
    async fetchOrders() {
      const res = await fetch(`${base}/orders`);
      this.orders = await res.json();
    },

    /**
     * Create a new service order via POST /api/orders.
     * The backend handles stock decrement and total calculation automatically.
     *
     * @param {Object} payload
     * @param {string} payload.customer_name - Name of the vehicle owner
     * @param {string} payload.vehicle       - Vehicle description (model, year, plates)
     * @param {Array}  payload.items         - Line items: [{ product_id, qty }, ...]
     *
     * @returns {Object} The newly created order with its items
     * @throws  {Error}  If the API returns a non-2xx response
     *
     * Example:
     *   await store.createOrder({
     *     customer_name: 'Juan Pérez',
     *     vehicle: 'Jetta 2018',
     *     items: [{ product_id: 1, qty: 2 }]
     *   });
     */
    async createOrder(payload) {
      const res = await fetch(`${base}/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      if (!res.ok) throw new Error('Failed to create order');

      const created = await res.json();

      // Refresh the orders list to reflect the new entry
      await this.fetchOrders();

      return created;
    },
  },
});
