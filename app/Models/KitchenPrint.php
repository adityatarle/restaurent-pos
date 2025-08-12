<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenPrint extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'type', 'print_content', 'user_id'];

    protected $casts = [
        'print_content' => 'array', // Store items as JSON
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}