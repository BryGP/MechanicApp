<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OrderItem Model
 *
 * Represents a single line item within an Order — i.e., a specific product
 * used during a workshop service. Stores the unit price at the time of sale
 * (snapshot pricing) so future product price changes don't affect closed orders.
 *
 * Relationships:
 *   - Belongs to one Order
 *   - Belongs to one Product
 *
 * @property int    $id         Primary key
 * @property int    $order_id   Foreign key ? orders.id (cascades on delete)
 * @property int    $product_id Foreign key ? products.id (restricted on delete)
 * @property int    $qty        Quantity of this product used
 * @property float  $unit_price Price per unit at the time the order was created
 * @property float  $subtotal   qty × unit_price (pre-calculated)
 */
class OrderItem extends Model
{
    /**
     * Attributes that can be mass-assigned.
     * Note: qty field in the DB is 'quantity' but the controller uses 'qty' as input alias.
     */
    protected $fillable = ['order_id', 'product_id', 'quantity', 'qty', 'unit_price', 'subtotal'];

    protected $appends = ['qty'];

    public function getQtyAttribute()
    {
        return $this->attributes['quantity'] ?? null;
    }

    public function setQtyAttribute($value)
    {
        $this->attributes['quantity'] = $value;
    }

    /**
     * The product this line item references.
     * Deletion of the product is restricted while it is referenced by any order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The parent order this line item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
