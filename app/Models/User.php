<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Add role
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Helper methods for role checking
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isReception(): bool
    {
        return $this->role === 'reception';
    }

    public function isWaiter(): bool
    {
        return $this->role === 'waiter';
    }

    public function ordersTaken() // As waiter
{
    return $this->hasMany(Order::class, 'user_id');
}

public function kitchenPrints()
{
    return $this->hasMany(KitchenPrint::class, 'user_id');
}

public function notifications()
{
    return $this->hasMany(Notification::class, 'user_id')->orderBy('created_at', 'desc');
}
}