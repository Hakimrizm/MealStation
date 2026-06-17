<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $menuId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        // Cek apakah user sudah mereview menu ini di order yang sama
        $existingReview = Review::where('user_id', Auth::id())
            ->where('menu_id', $menuId)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah memberikan ulasan untuk pesanan ini.'], 400);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'menu_id' => $menuId,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Ulasan berhasil dikirim!', 'data' => $review]);
    }
}
