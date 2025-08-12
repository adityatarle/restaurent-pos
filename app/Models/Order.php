<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'restaurant_table_id', 'user_id', 'customer_count', 'status',
        'total_amount', 'notes', 'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function restaurantTable()
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function waiter() // User who took the order
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items() // Alias for orderItems
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function kitchenPrints()
    {
        return $this->hasMany(KitchenPrint::class);
    }

    public function calculateTotal()
    {
        $this->total_amount = $this->orderItems()->sum(\DB::raw('quantity * price_at_order'));
        $this->save();
    }

    public function getUnprintedItemsCount()
{
    return $this->orderItems()
                ->where('printed_to_kitchen', false)
                ->where('status', '!=', 'cancelled') // Assuming 'pending' or 'confirmed'
                ->count();
}
}