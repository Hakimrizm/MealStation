<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOptionGroup extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function items()
    {
        return $this->hasMany(MenuOptionItem::class)->orderBy('sort_order');
    }
}
