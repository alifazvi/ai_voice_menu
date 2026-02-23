<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id','customer_id','menu_id','status','food_name','quantity','size','total_amount','subtotal','tax','discount','delivery_fee','table_number','guest_count','delivery_address','instructions','items','booked_at','placed_at','payment_method','notes'
    ];

    protected $casts = [
        'items' => 'array',
        'booked_at' => 'datetime',
        'placed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'quantity' => 'decimal:2',
        'guest_count' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
