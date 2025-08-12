<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;
    protected $fillable = ['category_id', 'name', 'description', 'price', 'is_available', 'image_path'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(InventoryItem::class, 'menu_item_ingredients')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}