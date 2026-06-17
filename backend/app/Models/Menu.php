<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $guarded = ['id'];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function optionGroups()
    {
        return $this->hasMany(MenuOptionGroup::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Accessor untuk rating menu
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1) ?? 0;
    }
}
