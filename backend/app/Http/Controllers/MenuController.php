<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        $menu = $request->user()->menus()->create([
            'name'        => $request->name,
            'image'       => $imagePath,
            'description' => $request->description,
            'price'       => $request->price,
            'category'    => $request->category,
            'rating'      => $request->rating ?? 0,
            'is_hot'      => $request->is_hot ?? false,
            'is_available' => true,
        ]);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan',
            'menu'    => $menu
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Menu $menu)
    {
        return response()->json($menu->load('tenant:id,name'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'name'  => 'sometimes|required',
            'price' => 'sometimes|required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'name',
            'description',
            'price',
            'category',
            'rating',
            'is_hot',
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }

            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($data);

        return response()->json([
            'message' => 'Menu berhasil diupdate',
            'menu'    => $menu->fresh()
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Menu $menu)
    {
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        // 🗑️ Hapus data menu
        $menu->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus'
        ]);
    }

    public function myMenus(Request $request)
    {
        return response()->json(
            $request->user()
                ->menus()
                ->latest()
                ->get()
        );
    }

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
