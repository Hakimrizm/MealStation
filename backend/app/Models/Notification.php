<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'read_at',
        'type',
        'estimation_minutes',
        'order_id'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}