<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id', 'name', 'description', 'attachments', 'is_active', 'vapi_file_ids'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_active' => 'boolean',
        'vapi_file_ids' => 'array',
        'pricing_taxes' => 'array',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}