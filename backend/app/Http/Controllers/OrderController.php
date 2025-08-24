<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index() {
        return Order::with('items')->latest()->get();
    }

    public function store(Request $request) {
        $data = $request->validate([
            'customer_name'=>'nullable|string',
            'vehicle'=>'nullable|string',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|integer|exists:products,id',
            'items.*.qty'=>'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'customer_name'=>$data['customer_name'] ?? null,
                'vehicle'=>$data['vehicle'] ?? null,
                'status'=>'open',
                'total'=>0,
            ]);

            $total = 0;
            foreach ($data['items'] as $it) {
                $product = Product::findOrFail($it['product_id']);
                $unit = $product->price;
                $subtotal = $unit * $it['qty'];

                // descuenta stock
                $product->decrement('stock', $it['qty']);

                $order->items()->create([
                    'product_id'=>$product->id,
                    'qty'=>$it['qty'],
                    'unit_price'=>$unit,
                    'subtotal'=>$subtotal,
                ]);
                $total += $subtotal;
            }

            $order->update(['total'=>$total]);
            return $order->load('items');
        });
    }

    public function show(Order $order) {
        return $order->load('items');
    }

    public function update(Request $request, Order $order) {
        $order->update($request->only('customer_name','vehicle','status'));
        return $order->load('items');
    }

    public function destroy(Order $order) {
        $order->delete();
        return response()->json(['deleted'=>true]);
    }
}