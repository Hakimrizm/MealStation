<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // AMBIL LIST NOTIF
    public function index()
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            $notifications->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'time' => $n->created_at->format('H:i'),

                    // INI KUNCI READ/UNREAD
                    'read' => $n->read_at !== null,
                ];
            })
        );
    }

    // MARK AS READ
    public function markAsRead($id)
    {
        $userId = Auth::id();

        $notif = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notif) {
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }

        $notif->update([
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}