<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'vehicle',
        'status',
        'total',
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeOpen($query) {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query) {
        return $query->where('status', 'closed');
    }
}