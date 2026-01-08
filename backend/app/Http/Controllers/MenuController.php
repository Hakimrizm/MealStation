<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // 🟢 Public (user & tenant)
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
        ]);

        $menu = $request->user()->menus()->create([
            'name'        => $request->name,
            'image'       => $request->image,
            'description' => $request->description,
            'price'       => $request->price,
            'category'    => $request->category,
            'rating'      => $request->rating ?? 0,
            'is_hot'      => $request->is_hot ?? false,
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

        $menu->update($request->all());

        return response()->json([
            'message' => 'Menu berhasil diupdate',
            'menu'    => $menu
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
}
