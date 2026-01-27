<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }

    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->min_stock_level;
    }
}
