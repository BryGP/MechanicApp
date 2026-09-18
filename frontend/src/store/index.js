import { defineStore } from 'pinia'

const base = import.meta.env.VITE_API_URL

export const useStore = defineStore('main', {
  state: () => ({
    products: [],
    orders: [],
    expenses: [],
    reportTemplates: [],
  }),

  getters: {
    lowStockProducts: (s) => s.products.filter(p => p.stock <= p.min_stock),
    openOrders: (s) => s.orders.filter(o => o.status === 'open' || o.status === 'in_progress'),
    totalRevenue: (s) => s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0),
    totalExpenses: (s) => s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0),
    netProfit: (s) => {
      const rev = s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0)
      const exp = s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0)
      return rev - exp
    },
    marginPct: (s) => {
      const rev = s.orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0)
      if (rev === 0) return 0
      const exp = s.expenses.reduce((sum, e) => sum + parseFloat(e.amount || 0), 0)
      return ((rev - exp) / rev) * 100
    },
  },

  actions: {
    async fetchProducts() {
      const res = await fetch(`${base}/products`)
      this.products = await res.json()
    },
    async fetchOrders() {
      const res = await fetch(`${base}/orders`)
      this.orders = await res.json()
    },

    // Products CRUD
    async createProduct(data) {
      const res = await fetch(`${base}/products`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        throw new Error(err.message || 'Error al crear producto')
      }
      await this.fetchProducts()
    },
    async updateProduct(id, data) {
      const res = await fetch(`${base}/products/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!res.ok) throw new Error('Error al actualizar producto')
      await this.fetchProducts()
    },
    async deleteProduct(id) {
      const res = await fetch(`${base}/products/${id}`, { method: 'DELETE' })
      if (!res.ok) throw new Error('No se puede eliminar: el producto tiene ordenes asociadas')
      await this.fetchProducts()
    },

    // Orders CRUD
    async createOrder(payload) {
      const res = await fetch(`${base}/orders`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        throw new Error(err.message || 'Error al crear orden')
      }
      const created = await res.json()
      await this.fetchOrders()
      await this.fetchProducts()
      return created
    },
    async updateOrderStatus(id, status) {
      const res = await fetch(`${base}/orders/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
      })
      if (!res.ok) throw new Error('Error al actualizar orden')
      await this.fetchOrders()
    },
    async deleteOrder(id) {
      const res = await fetch(`${base}/orders/${id}`, { method: 'DELETE' })
      if (!res.ok) throw new Error('Error al eliminar orden')
      await this.fetchOrders()
    },

    // Expenses / Accounting
    async fetchExpenses() {
      try {
        const res = await fetch(`${base}/expenses`)
        if (res.ok) {
          this.expenses = await res.json()
        }
      } catch {
        this.expenses = []
      }
    },
    async createExpense(data) {
      const res = await fetch(`${base}/expenses`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        throw new Error(err.message || 'Error al registrar egreso')
      }
      await this.fetchExpenses()
    },
    async deleteExpense(id) {
      const res = await fetch(`${base}/expenses/${id}`, { method: 'DELETE' })
      if (!res.ok) throw new Error('Error al eliminar egreso')
      await this.fetchExpenses()
    },

    // Reports & SQL Templates
    async fetchReportTemplates() {
      try {
        const res = await fetch(`${base}/reports/templates`)
        if (res.ok) {
          this.reportTemplates = await res.json()
        }
      } catch {
        this.reportTemplates = []
      }
    },
    async createReportTemplate(data) {
      const res = await fetch(`${base}/reports/templates`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        throw new Error(err.message || 'Error al guardar plantilla')
      }
      await this.fetchReportTemplates()
    },
    async executeSqlReport(sql_query) {
      const res = await fetch(`${base}/reports/execute`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sql_query }),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        throw new Error(err.message || 'Error al ejecutar consulta SQL')
      }
      return await res.json()
    },
  },
})