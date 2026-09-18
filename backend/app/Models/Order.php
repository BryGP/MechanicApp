<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order Model
 *
 * Represents a workshop service order for a customer vehicle.
 * An order groups one or more OrderItems (products used in the service)
 * and tracks the overall status of the job from open to delivery.
 *
 * Status lifecycle:
 *   open  ?  in_progress  ?  done  ?  delivered
 *
 * @property int    $id            Auto-incremented primary key
 * @property string $customer_name Name of the vehicle owner
 * @property string $vehicle       Vehicle description (model, year, plates, etc.)
 * @property string $status        Current job status: open | in_progress | done | delivered
 * @property float  $total         Sum of all OrderItem subtotals (auto-calculated on create)
 */
class Order extends Model
{
    /**
     * Attributes that can be mass-assigned.
     */
    protected $fillable = [
        'customer_name',
        'vehicle',
        'status',
        'total',
    ];

    /**
     * An order contains one or more line items (products used in the service).
     * Deleting an order cascades to its items (configured at DB level).
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Local scope: filter only orders that are still open (not yet started).
     * Usage: Order::open()->get()
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Local scope: filter only orders that have been delivered/closed.
     * Usage: Order::closed()->get()
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
