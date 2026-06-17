<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function getIsReviewedAttribute()
    {
        // Cek apakah tabel ulasan memiliki data dengan order_id dan menu_id ini
        return \App\Models\Review::where('order_id', $this->order_id)
            ->where('menu_id', $this->menu_id)
            ->exists();
    }
}
