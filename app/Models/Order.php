<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    //
    use HasFactory;
  
    protected $fillable = [
        'user_id',
        'order_no',
        'address_id',
        'grand_total',
        'order_status',
        'payment_status',
        'payment_method',
        'order_no',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'paid_at',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount'
    ];

    public function items()
{
    return $this->hasMany(OrderItem::class);
}

public function address()
{
    return $this->belongsTo(Address::class);
}
public function user(){
    return $this->belongsTo(User::class);
}

protected $casts = [
    'paid_at' => 'datetime',
];

}
