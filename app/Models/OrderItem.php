<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    use HasFactory;

    protected $table = 'orderitems';

    protected $fillable = [
        'order_id',
        'product_id',
        'color_id',
        'status',
        'size_id',
        'price',
        'qty',
        'total',
        'return_requested',
        'delivered_at',
        'return_requested_at',
        'is_returned',
        'returned_at',
        'refund_status',
        'refund_amount',

    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected $casts = [
        'delivered_at' => 'datetime',
    ];
}
