<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // USER: checkout dari cart (buat order)
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        // Ambil semua menu yang dibeli
        $menuIds = collect($request->items)->pluck('menu_id')->unique()->values();
        $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

        // Validasi: semua item harus dari tenant yang sama
        $tenantId = null;
        foreach ($request->items as $it) {
            $menu = $menus[$it['menu_id']] ?? null;
            if (!$menu) {
                return response()->json(['message' => 'Menu tidak ditemukan'], 422);
            }
            $tenantId = $tenantId ?? $menu->user_id;
            if ($menu->user_id !== $tenantId) {
                return response()->json([
                    'message' => 'Checkout harus dari 1 tenant yang sama'
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $user, $tenantId, $menus) {
            $order = Order::create([
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'status' => 'new',
                'grand_total' => 0,
                'notes' => $request->notes,
            ]);

            $grand = 0;

            foreach ($request->items as $it) {
                $menu = $menus[$it['menu_id']];
                $qty = (int)$it['qty'];
                $options = $it['options'] ?? [];
                $optionTotal = collect($options)->sum('price');

                $unit = (int)$menu->price + $optionTotal;
                $qty = (int)$it['qty'];
                $sub = $unit * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'unit_price' => $menu->price,
                    'qty' => $qty,
                    'notes' => $it['notes'] ?? null,
                    'options' => $options, // ✅ sekarang aman karena cast array
                    'option_total' => $optionTotal,
                    'subtotal' => $sub,
                ]);

                $grand += $sub;
            }

            $order->update(['grand_total' => $grand]);

            return response()->json([
                'message' => 'Order berhasil dibuat',
                'order' => $order->load(['items', 'tenant:id,name', 'user:id,name'])
            ], 201);
        });
    }

    // USER: list order milik user (riwayat transaksi)
    public function myOrders(Request $request)
    {
        $orders = Order::with(['items', 'tenant:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($order) {
                // LOGIKA PERBAIKAN: Hentikan estimasi jika sudah selesai atau batal
                if (in_array($order->status, ['done', 'cancelled'], true)) {
                    $order->estimation_time = null; 
                } 
                // Jika masih proses, baru dikonversi ke ISO8601
                elseif ($order->estimation_time) {
                    $order->estimation_time = \Carbon\Carbon::parse($order->estimation_time)->toIso8601String();
                }
                
                return $order;
            });

        return response()->json($orders);
    }

    // USER: detail order
    public function myOrderShow(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load([
            'items',
            'tenant:id,name,qris_image,qris_name'
        ]);

        // LOGIKA PERBAIKAN
        if (in_array($order->status, ['done', 'cancelled'], true)) {
            $order->estimation_time = null;
        } elseif ($order->estimation_time) {
            $order->estimation_time = \Carbon\Carbon::parse($order->estimation_time)->toIso8601String();
        }

        return response()->json($order);
    }

    // TENANT: list order yang masuk ke tenant
    public function tenantOrders(Request $request)
    {
        $orders = Order::with(['items', 'user:id,name'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($order) {
                // LOGIKA PERBAIKAN: Paksa null jika status sudah done/cancelled
                if (in_array($order->status, ['done', 'cancelled'], true)) {
                    $order->estimation_time = null;
                }
                return $order;
            });

        return response()->json($orders);
    }

    // TENANT: update status (approve -> process, done, cancel)
    public function tenantUpdateStatus(Request $request, Order $order)
    {
        if ($order->tenant_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'status' => 'required|in:new,process,done,cancelled',
            'estimation_time' => 'nullable|integer', 
        ]);

        $from = $order->status;
        $to = $request->status;

        $allowed = [
            'new' => ['process', 'cancelled'],
            'process' => ['done', 'cancelled'],
            'done' => [],
            'cancelled' => [],
        ];

        if (!in_array($to, $allowed[$from], true) && $to !== $from) {
            return response()->json([
                'message' => "Tidak bisa ubah status dari {$from} ke {$to}"
            ], 422);
        }

        $updateData = ['status' => $to];

        // LOGIKA PERBAIKAN: Simpan sebagai string waktu yang bersih
        if ($request->has('estimation_time') && $to === 'process') {
            $minutes = (int) $request->estimation_time;
            
            // Kita format ke 'Y-m-d H:i:s' agar JavaScript bisa membacanya dengan pasti
            $updateData['estimation_time'] = now()->addMinutes($minutes)->format('Y-m-d H:i:s');
        }

        $order->update($updateData);

        // Load data terbaru
        $order = $order->fresh()->load(['items', 'user:id,name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Status order diupdate',
            'order' => $order // Kembalikan object order penuh agar frontend dapat semua field
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // tidak boleh bayar kalau sudah cancelled/done
        if (in_array($order->status, ['done','cancelled'], true)) {
            return response()->json(['message' => 'Order tidak bisa dibayar'], 422);
        }

        $request->validate([
            'method' => 'required|in:cash,qris',
            'proof' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $method = $request->method;

        if ($method === 'qris') {
            if (!$request->hasFile('proof')) {
                return response()->json(['message' => 'Bukti bayar wajib untuk QRIS'], 422);
            }

            // hapus proof lama kalau ada
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $request->file('proof')->store('payments', 'public');

            $order->update([
                'payment_method' => 'qris',
                'payment_status' => 'waiting_confirmation',
                'payment_proof' => $path,
            ]);

            return response()->json([
                'message' => 'Bukti bayar dikirim, menunggu konfirmasi tenant',
                'order' => $order->fresh(),
            ]);
        }

        // CASH
        $order->update([
            'payment_method' => 'cash',
            'payment_status' => 'waiting_confirmation', // atau 'unpaid' kalau mau bayar di tempat
        ]);

        return response()->json([
            'message' => 'Metode tunai dipilih, menunggu konfirmasi tenant',
            'order' => $order->fresh(),
        ]);
    }

    public function tenantVerifyPayment(Request $request, Order $order)
    {
        if ($order->tenant_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($request->action === 'approve') {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                // setelah paid, tenant boleh ubah status ke process
            ]);
        } else {
            $order->update([
                'payment_status' => 'rejected',
            ]);
        }

        return response()->json([
            'message' => 'Pembayaran diupdate',
            'order' => $order->fresh(),
        ]);
    }
}
