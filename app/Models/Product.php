<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'stock'
    ];

    // Price per site
    public function sites()
    {
        return $this->belongsToMany(Site::class)
            ->withPivot('price')
            ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helpers
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
