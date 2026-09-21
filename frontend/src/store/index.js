/**
 * @fileoverview Pinia Global State Store
 * @module store/index
 * @description Centralized reactive store managing workshop products, service catalog,
 * work orders, operational expenses, cash-flow metrics, and SQL analytics reports.
 */

import { defineStore } from 'pinia'
import { apiClient } from '../utils/apiClient'

/**
 * Main application store definition using Pinia.
 */
export const useStore = defineStore('main', {
  /**
   * Reactive state tree for the automotive workshop application.
   * @returns {Object} Initial state object
   */
  state: () => ({
    /**
     * Complete inventory of physical parts and catalog of labor services.
     * @type {Array<Object>}
     */
    products: [],

    /**
     * All customer work orders with line items and fulfillment statuses.
     * @type {Array<Object>}
     */
    orders: [],

    /**
     * Log of operational disbursements, overhead costs, parts purchases, and payroll.
     * @type {Array<Object>}
     */
    expenses: [],

    /**
     * Auto-discovered SQL report templates available in the system.
     * @type {Array<Object>}
     */
    reportTemplates: [],
  }),

  /**
   * Computed getters for derived business intelligence and financial metrics.
   */
  getters: {
    /**
     * Filters physical products whose stock level is at or below the safety threshold.
     * Explicitly ignores labor services (is_service = 1) to avoid false depletion alerts.
     * @param {Object} s - Pinia state
     * @returns {Array<Object>} Critical stock physical parts
     */
    lowStockProducts: (s) => s.products.filter(p => !p.is_service && p.stock <= p.min_stock),

    /**
     * Filters work orders currently active in the workshop ('open' or 'in_progress').
     * @param {Object} s - Pinia state
     * @returns {Array<Object>} In-flight repair orders
     */
    openOrders: (s) => s.orders.filter(o => o.status === 'open' || o.status === 'in_progress'),

    /**
     * Calculates the cumulative gross revenue generated across all recorded orders.
     * @param {Object} s - Pinia state
     * @returns {number} Gross revenue in MXN
     */
    totalRevenue: (s) => s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0),

    /**
     * Calculates the cumulative operational expenses logged in accounting.
     * @param {Object} s - Pinia state
     * @returns {number} Total expenses in MXN
     */
    totalExpenses: (s) => s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0),

    /**
     * Computes the net profit (surplus or deficit) by subtracting expenses from gross revenue.
     * @param {Object} s - Pinia state
     * @returns {number} Net operating profit in MXN
     */
    netProfit: (s) => {
      const rev = s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0)
      const exp = s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0)
      return rev - exp
    },

    /**
     * Computes the operating margin percentage: ((Revenue - Expenses) / Revenue) * 100.
     * Returns 0 if total revenue is zero.
     * @param {Object} s - Pinia state
     * @returns {number} Operating profit margin percentage
     */
    marginPct: (s) => {
      const rev = s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0)
      if (rev === 0) return 0
      const exp = s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0)
      return ((rev - exp) / rev) * 100
    },
  },

  /**
   * Asynchronous actions for REST API synchronization and business workflows.
   */
  actions: {
    // =========================================================================
    // SECTION 1: PRODUCTS & SERVICES CATALOG ACTIONS
    // =========================================================================

    /**
     * Fetches the complete catalog of products and services from the backend API.
     * @async
     */
    async fetchProducts() {
      this.products = await apiClient.get('/products')
    },

    /**
     * Creates a new product or labor service item via the API and refreshes catalog.
     * 
     * @async
     * @param {Object} data - Product creation payload
     * @throws {Error} Detailed validation message if the creation fails
     */
    async createProduct(data) {
      await apiClient.post('/products', data)
      await this.fetchProducts()
    },

    /**
     * Updates an existing product or service and synchronizes the local store.
     * 
     * @async
     * @param {number|string} id - Product identifier
     * @param {Object} data - Updated attributes
     * @throws {Error} If validation fails or item is not found
     */
    async updateProduct(id, data) {
      await apiClient.put(`/products/${id}`, data)
      await this.fetchProducts()
    },

    /**
     * Deletes a product from catalog if no active work orders reference it.
     * 
     * @async
     * @param {number|string} id - Product identifier
     * @throws {Error} If foreign key constraints or server error prevent deletion
     */
    async deleteProduct(id) {
      await apiClient.delete(`/products/${id}`)
      await this.fetchProducts()
    },

    // =========================================================================
    // SECTION 2: WORK ORDERS CRUD ACTIONS
    // =========================================================================

    /**
     * Fetches all registered work orders from the backend API.
     * @async
     */
    async fetchOrders() {
      this.orders = await apiClient.get('/orders')
    },

    /**
     * Registers a new work order with line items, automatically recalculating
     * physical inventory and order totals atomically on the server.
     * 
     * @async
     * @param {Object} payload - Order parameters (customer_name, vehicle, notes, items)
     * @returns {Promise<Object>} Created work order entity
     * @throws {Error} On validation or stock deduction errors
     */
    async createOrder(payload) {
      const created = await apiClient.post('/orders', payload)
      await this.fetchOrders()
      await this.fetchProducts()
      return created
    },

    /**
     * Updates the fulfillment status of a work order (open -> in_progress -> done -> delivered).
     * 
     * @async
     * @param {number|string} id - Order identifier
     * @param {'open'|'in_progress'|'done'|'delivered'} status - Target workflow status
     * @throws {Error} If server reject the status transition
     */
    async updateOrderStatus(id, status) {
      await apiClient.put(`/orders/${id}`, { status })
      await this.fetchOrders()
    },

    /**
     * Permanently deletes a work order and its cascaded order items.
     * Enforces administrator authorization.
     * 
     * @async
     * @param {number|string} id - Order identifier to remove
     * @param {string} [adminPin] - Optional verified administrator PIN
     * @throws {Error} If server deletion fails
     */
    async deleteOrder(id, adminPin) {
      await apiClient.delete(`/orders/${id}`, { adminPin })
      await this.fetchOrders()
    },

    // =========================================================================
    // SECTION 3: EXPENSES & ACCOUNTING ACTIONS
    // =========================================================================

    /**
     * Fetches all registered operating expenses sorted chronologically.
     * @async
     */
    async fetchExpenses() {
      try {
        this.expenses = await apiClient.get('/expenses')
      } catch {
        this.expenses = []
      }
    },

    /**
     * Registers a new operating disbursement in the accounting ledger.
     * 
     * @async
     * @param {Object} data - Expense payload (concept, category, amount, payment_method, reference, expense_date)
     * @throws {Error} On duplicate entry or validation errors
     */
    async createExpense(data) {
      await apiClient.post('/expenses', data)
      await this.fetchExpenses()
    },

    /**
     * Removes an operational expense record from the ledger.
     * Enforces administrator authorization.
     * 
     * @async
     * @param {number|string} id - Expense record identifier
     * @param {string} [adminPin] - Optional verified administrator PIN
     * @throws {Error} If server fails to delete the entry
     */
    async deleteExpense(id, adminPin) {
      await apiClient.delete(`/expenses/${id}`, { adminPin })
      await this.fetchExpenses()
    },

    // =========================================================================
    // SECTION 4: ANALYTICS & REPORTS ENGINE ACTIONS
    // =========================================================================

    /**
     * Retrieves the auto-discovered SQL report definitions from the backend.
     * @async
     */
    async fetchReports() {
      try {
        this.reportTemplates = await apiClient.get('/reports')
      } catch {
        this.reportTemplates = []
      }
    },

    /**
     * Executes a specific business report by ID and returns its tabular analytical dataset.
     * 
     * @async
     * @param {string} id - Unique identifier of the report template
     * @returns {Promise<Object>} Execution results including dataset columns and rows
     * @throws {Error} If report execution fails
     */
    async runReport(id) {
      return await apiClient.get(`/reports/${id}/run`)
    },
  },
})