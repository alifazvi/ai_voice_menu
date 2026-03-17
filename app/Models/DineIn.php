<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DineIn extends Model
{
    protected $table = 'dine_in';

    protected $fillable = [
        'customer_id',
        'table_number',
        'seats',
        'location',
        'is_available',
        'booking_date',
        'booking_slot',
        'special_request',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'seats' => 'integer',
        'is_available' => 'boolean',
        'booking_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
