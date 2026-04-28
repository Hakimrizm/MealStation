<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Tampilkan semua menu (untuk sisi Customer)
     */
    public function index()
    {
        return response()->json(
            Menu::with('tenant:id,name')
                ->latest()
                ->get()
        );
    }

    /**
     * Detail menu beserta opsi topping
     */
    public function show($id)
    {
        // Kita ambil data menu beserta tenant-nya
        $menu = Menu::with(['tenant:id,name', 'optionGroups.items'])->find($id);

        // Jika menu tidak ketemu (ID salah), beri response error yang jelas
        if (!$menu) {
            return response()->json([
                'message' => 'Menu tidak ditemukan di database'
            ], 404);
        }

        // PENTING: Langsung kembalikan $menu tanpa dibungkus array ['menu' => ...]
        // Ini agar Frontend bisa langsung baca data.name, data.price, dll.
        return response()->json($menu);
    }

    /**
     * Simpan menu baru (Store)
     */
    public function store(Request $request)
    {
        // Decode JSON jika option_groups dikirim sebagai string
        if (is_string($request->option_groups)) {
            $decoded = json_decode($request->option_groups, true);
            $request->merge([
                'option_groups' => is_array($decoded) ? $decoded : []
            ]);
        }

        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'option_groups' => 'nullable|array',
            'option_groups.*.label' => 'required|string|max:255',
            'option_groups.*.input_type' => 'required|in:radio,checkbox,select,switch,number,text,textarea',
        ]);

        return DB::transaction(function () use ($request) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menus', 'public');
            }

            $menu = $request->user()->menus()->create([
                'name'         => $request->name,
                'image'        => $imagePath,
                'description'  => $request->description,
                'price'        => $request->price,
                'category'     => $request->category,
                'rating'       => $request->rating ?? 0,
                'is_hot'       => $request->is_hot ?? false,
                'is_available' => true, // Default menu baru tersedia
            ]);

            foreach ($request->option_groups ?? [] as $groupIndex => $groupData) {
                $group = $menu->optionGroups()->create([
                    'label'       => $groupData['label'],
                    'input_type'  => $groupData['input_type'],
                    'is_required' => $groupData['is_required'] ?? false,
                    'min_select'  => $groupData['min_select'] ?? 0,
                    'max_select'  => $groupData['max_select'] ?? null,
                    'placeholder' => $groupData['placeholder'] ?? null,
                    'sort_order'  => $groupIndex,
                ]);

                foreach ($groupData['items'] ?? [] as $itemIndex => $itemData) {
                    $group->items()->create([
                        'label'            => $itemData['label'],
                        'value'            => $itemData['value'] ?? $itemData['label'],
                        'price_adjustment' => $itemData['price_adjustment'] ?? 0,
                        'is_default'       => $itemData['is_default'] ?? false,
                        'sort_order'       => $itemIndex,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Menu berhasil ditambahkan',
                'menu'    => $menu->load('optionGroups.items')
            ], 201);
        });
    }

    /**
     * Update data menu
     */
    public function update(Request $request, Menu $menu)
    {
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (is_string($request->option_groups)) {
            $decoded = json_decode($request->option_groups, true);
            $request->merge([
                'option_groups' => is_array($decoded) ? $decoded : []
            ]);
        }

        $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        return DB::transaction(function () use ($request, $menu) {
            // Ambil semua field yang mungkin berubah
            $data = $request->only([
                'name', 'description', 'price', 'category', 'rating', 'is_hot', 'is_available'
            ]);

            if ($request->hasFile('image')) {
                if ($menu->image) {
                    Storage::disk('public')->delete($menu->image);
                }
                $data['image'] = $request->file('image')->store('menus', 'public');
            }

            $menu->update($data);

            if ($request->exists('option_groups')) {
                $menu->optionGroups()->delete();
                foreach ($request->option_groups ?? [] as $groupIndex => $groupData) {
                    $group = $menu->optionGroups()->create([
                        'label'       => $groupData['label'],
                        'input_type'  => $groupData['input_type'],
                        'is_required' => $groupData['is_required'] ?? false,
                        'min_select'  => $groupData['min_select'] ?? 0,
                        'max_select'  => $groupData['max_select'] ?? null,
                        'sort_order'  => $groupIndex,
                    ]);

                    foreach ($groupData['items'] ?? [] as $itemIndex => $itemData) {
                        $group->items()->create([
                            'label'            => $itemData['label'],
                            'value'            => $itemData['value'] ?? $itemData['label'],
                            'price_adjustment' => $itemData['price_adjustment'] ?? 0,
                            'is_default'       => $itemData['is_default'] ?? false,
                            'sort_order'       => $itemIndex,
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Menu berhasil diupdate',
                'menu'    => $menu->fresh()->load('optionGroups.items')
            ]);
        });
    }

    /**
     * Hapus menu
     */
    public function destroy(Request $request, Menu $menu)
    {
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

    /**
     * Tampilkan menu milik tenant yang login
     */
    public function myMenus(Request $request)
    {
        return response()->json(
            $request->user()
                ->menus()
                ->with('optionGroups.items')
                ->latest()
                ->get()
        );
    }

    /**
     * FITUR TOGGLE: Ubah status tersedia/habis dengan cepat
     */
    public function toggleAvailability($id)
    {
        $menu = Menu::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        
        $menu->update([
            'is_available' => !$menu->is_available
        ]);

        return response()->json([
            'status' => 'success',
            'is_available' => $menu->is_available,
            'message' => 'Status menu berhasil diubah!'
        ]);
    }
}