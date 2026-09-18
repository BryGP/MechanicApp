<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * ProductController
 *
 * Handles all CRUD operations for workshop inventory products.
 * Registered as an API resource route in routes/api.php, which maps
 * standard HTTP verbs to these methods automatically:
 *
 *   GET    /api/products          ? index()
 *   POST   /api/products          ? store()
 *   GET    /api/products/{id}     ? show()    (not implemented)
 *   PUT    /api/products/{id}     ? update()
 *   DELETE /api/products/{id}     ? destroy()
 */
class ProductController extends Controller
{
    /**
     * Return all products sorted alphabetically by name.
     * Used by the frontend to populate the inventory list and
     * the product picker when creating a new order.
     */
    public function index()
    {
        return Product::orderBy('name')->get();
    }

    /**
     * Create a new product in the inventory.
     *
     * Validation rules:
     *   - name      : required, string, max 255 chars
     *   - sku       : required, unique across products table
     *   - price     : required, numeric, non-negative
     *   - stock     : required, integer, non-negative
     *   - min_stock : optional, integer (default threshold for low-stock alerts)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'sku'       => 'required|string|max:255|unique:products,sku',
            'price'     => 'required|numeric|min:0',
            'stock'     => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        Product::create($data);
    }

    /**
     * Update an existing product's attributes.
     * All fields are optional (PATCH-style): only provided fields are updated.
     * SKU uniqueness is validated while ignoring the current product's own SKU.
     *
     * @param  Request  $request
     * @param  Product  $product  Route-model binding resolves the product by ID
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'sku'       => "sometimes|string|max:255|unique:products,sku,{$product->id}",
            'price'     => 'sometimes|numeric|min:0',
            'stock'     => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
        ]);

        $product->update($data);
        return $product;
    }

    /**
     * Return a single product by ID.
     * Currently not implemented — reserved for future detail view.
     *
     * @param  Product  $product
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing a product.
     * Not used in API-only mode (reserved for Blade/SSR if needed).
     *
     * @param  Product  $product
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Permanently delete a product from the inventory.
     * Note: deletion will fail if the product is referenced by any existing
     * order item (enforced by DB foreign key with onDelete RESTRICT).
     *
     * @param  Product  $product
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['deleted' => true]);
    }
}
