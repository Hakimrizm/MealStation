<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Menggunakan fungsi chat yang sudah bersih
    public function chat($receiverId)
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        // Update ke is_read = 1 karena database Anda menggunakan is_read
        Message::where('sender_id', $receiverId)
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // Ambil semua pesan
        $messages = Message::with(['sender', 'receiver'])
            ->where(function ($q) use ($userId, $receiverId) {
                $q->where('sender_id', $userId)->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($q) use ($userId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request)
    {
        if (!Auth::check()) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate([
            'receiver_id' => 'required|integer',
            'message' => 'required|string'
        ]);

        // Simpan dengan is_read = 0 (default sesuai tabel)
        $msg = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => (int) $request->receiver_id,
            'message' => $request->message,
            'type' => 'text',
            'is_read' => 0 
        ]);

        return response()->json(['success' => true, 'data' => $msg]);
    }

    public function chatList()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json([]);

        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $messages->groupBy(function ($msg) use ($userId) {
            return $msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id;
        });

        return response()->json(
            $grouped->map(function ($group) use ($userId) {
                $msg = $group->first();
                $partner = $msg->sender_id == $userId ? $msg->receiver : $msg->sender;
                if (!$partner) return null;

                // Hitung unread berdasarkan is_read = 0
                $unreadCount = $group->where('receiver_id', $userId)
                                     ->where('is_read', 0)
                                     ->count();

                return [
                    'user_id' => $partner->id,
                    'name' => $partner->name ?? 'Unknown',
                    'last_message' => $msg->message,
                    'time' => $msg->created_at->diffForHumans(),
                    'unread' => $unreadCount,
                ];
            })->filter()->values()
        );
    }
}