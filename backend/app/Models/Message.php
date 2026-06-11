<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'chats';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'is_read',
    ];

    public $timestamps = true;

    protected $with = ['sender', 'receiver'];

    // ========================
    // RELATIONSHIPS
    // ========================
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')
            ->select('id', 'name', 'email');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id')
            ->select('id', 'name', 'email');
    }

    // ========================
    // SCOPES
    // ========================
    public function scopeConversation($query, $userId, $receiverId)
    {
        return $query->where(function ($q) use ($userId, $receiverId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($userId, $receiverId) {
            $q->where('sender_id', $receiverId)
              ->where('receiver_id', $userId);
        })->orderBy('created_at', 'asc');
    }

    public function scopeUnread($query, $userId)
    {
        return $query->where('receiver_id', $userId)
                     ->where('is_read', 0);
    }
}