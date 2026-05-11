<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // =========================
    // CHAT ROOM (ISI PESAN)
    // =========================
   public function chat($receiverId)
{
    $userId = Auth::id();

    $messages = Message::with('product')
        ->where(function ($q) use ($userId, $receiverId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $receiverId);
        })
        ->orWhere(function ($q) use ($userId, $receiverId) {
            $q->where('sender_id', $receiverId)
              ->where('receiver_id', $userId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'messages' => $messages
    ]);
}

    // =========================
    // KIRIM PESAN
    // =========================
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'message' => 'required'
        ]);

        $msg = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'product_id' => $request->product_id ?? null,
            'is_read' => false
        ]);

        return response()->json($msg);
    }

    // =========================
    // CHAT LIST (INBOX)
    // =========================
    public function chatList()
    {
        $userId = Auth::id();

        $messages = Message::with(['sender', 'receiver'])
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id == $userId
                    ? $msg->receiver_id
                    : $msg->sender_id;
            })
            ->map(function ($group) use ($userId) {
                $msg = $group->first();
                $partner = $msg->sender_id == $userId ? $msg->receiver : $msg->sender;

                return [
                    'user_id' => $partner->id,
                    'name' => $partner->name,
                    'last_message' => $msg->message,
                    'time' => $msg->created_at->diffForHumans(),
                ];
            })
            ->values();

        return response()->json($messages);
    }
}