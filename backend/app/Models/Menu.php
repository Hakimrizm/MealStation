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
}
