<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOptionItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(MenuOptionGroup::class, 'menu_option_group_id');
    }
}
