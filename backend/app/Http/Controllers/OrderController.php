<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * OrderController
 *
 * Manages workshop service orders. Each order belongs to a customer/vehicle
 * and contains one or more line items (products used in the service).
 *
 * Registered as an API resource in routes/api.php:
 *
 *   GET    /api/orders          ? index()
 *   POST   /api/orders          ? store()
 *   GET    /api/orders/{id}     ? show()
 *   PUT    /api/orders/{id}     ? update()
 *   DELETE /api/orders/{id}     ? destroy()
 */
class OrderController extends Controller
{
    /**
     * Return all orders with their associated items, newest first.
     * Eager-loads 'items' to avoid N+1 queries when the frontend
     * renders order details.
     */
    public function index()
    {
        return Order::with('items')->latest()->get();
    }

    /**
     * Create a new order along with its line items in a single atomic transaction.
     *
     * Business logic performed inside the transaction:
     *   1. Validate input (customer, vehicle, and at least one item).
     *   2. Create the parent Order record with status = 'open'.
     *   3. For each item: fetch product price, calculate subtotal, decrement stock.
     *   4. Persist each OrderItem linked to the new order.
     *   5. Update the order's total with the sum of all subtotals.
     *
     * If any step fails (e.g. product not found, DB error), the entire
     * transaction is rolled back — no partial data is saved.
     *
     * Expected request body (JSON):
     * {
     *   "customer_name": "Juan Pérez",       // optional
     *   "vehicle": "Civic 2015",             // optional
     *   "items": [
     *     { "product_id": 1, "qty": 2 },
     *     { "product_id": 3, "qty": 1 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'        => 'nullable|string',
            'vehicle'              => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.qty'          => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($data) {
            // Step 1: Create the order shell with zero total
            $order = Order::create([
                'customer_name' => $data['customer_name'] ?? null,
                'vehicle'       => $data['vehicle'] ?? null,
                'status'        => 'open',
                'total'         => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $it) {
                // Step 2: Resolve product and snapshot its current price
                $product  = Product::findOrFail($it['product_id']);
                $unit     = $product->price;
                $subtotal = $unit * $it['qty'];

                // Step 3: Decrement inventory stock by quantity ordered
                $product->decrement('stock', $it['qty']);

                // Step 4: Create the order line item
                $order->items()->create([
                    'product_id' => $product->id,
                    'qty'        => $it['qty'],
                    'unit_price' => $unit,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Step 5: Save the computed total back to the order
            $order->update(['total' => $total]);

            return $order->load('items');
        });
    }

    /**
     * Return a single order with its line items.
     *
     * @param  Order  $order  Resolved via route-model binding by ID
     */
    public function show(Order $order)
    {
        return $order->load('items');
    }

    /**
     * Update mutable fields of an existing order.
     * Only customer_name, vehicle, and status can be changed here.
     * To modify items, the order must be recreated (current design).
     *
     * @param  Request  $request
     * @param  Order    $order
     */
    public function update(Request $request, Order $order)
    {
        $order->update($request->only('customer_name', 'vehicle', 'status'));
        return $order->load('items');
    }

    /**
     * Delete an order and all its line items.
     * Cascading delete is handled at the DB level (order_items.order_id has ON DELETE CASCADE).
     *
     * @param  Order  $order
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['deleted' => true]);
    }
}
