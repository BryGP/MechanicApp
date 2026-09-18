<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product Model
 *
 * Represents a spare part or supply item available in the workshop inventory.
 * Products can be added to orders via OrderItems, and their stock is
 * automatically decremented when an order is placed.
 *
 * @property int    $id
 * @property string $name       Human-readable product name (e.g. "Aceite 10W-30")
 * @property string $sku        Unique stock-keeping unit identifier (e.g. "ACE-10W30")
 * @property float  $price      Unit sale price (MXN)
 * @property int    $stock      Current units available in inventory
 * @property int    $min_stock  Minimum threshold — alerts when stock falls below this value
 */
class Product extends Model
{
    /**
     * Attributes that can be mass-assigned via Product::create() or $product->fill().
     * Prevents accidental overwrites of protected fields like id or timestamps.
     */
    protected $fillable = ['name', 'sku', 'price', 'stock', 'min_stock'];
}
