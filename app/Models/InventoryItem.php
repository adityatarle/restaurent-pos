<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; 

class InventoryItem extends Model
{
   use HasFactory, SoftDeletes;
    protected $fillable = [
        'name', 'description', 'category', 'unit_of_measure',
        'current_stock', 'reorder_level', 'average_cost_price'
    ];

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}
