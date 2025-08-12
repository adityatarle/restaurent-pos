<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventory_item_id', 'supplier_id', 'type', 'quantity',
        'cost_price_at_transaction', 'notes', 'transaction_date', 'user_id', 'order_item_id'
    ];
    protected $casts = ['transaction_date' => 'datetime'];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
