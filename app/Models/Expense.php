<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
  use HasFactory;
    protected $fillable = [
        'expense_category_id', 'description', 'amount', 'expense_date',
        'vendor_name', 'receipt_url', 'user_id'
    ];
    protected $casts = ['expense_date' => 'date'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
