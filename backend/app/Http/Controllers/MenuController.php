<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
    public function show($id)
    {
        $menu = Menu::with('optionGroups.items')->findOrFail($id);

        return response()->json([
            'menu' => $menu
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'option_groups.*.is_required' => 'nullable|boolean',
            'option_groups.*.min_select' => 'nullable|integer|min:0',
            'option_groups.*.max_select' => 'nullable|integer|min:0',
            'option_groups.*.placeholder' => 'nullable|string|max:255',
            'option_groups.*.min_value' => 'nullable|integer',
            'option_groups.*.max_value' => 'nullable|integer',

            'option_groups.*.items' => 'nullable|array',
            'option_groups.*.items.*.label' => 'required|string|max:255',
            'option_groups.*.items.*.value' => 'nullable|string|max:255',
            'option_groups.*.items.*.price_adjustment' => 'nullable|numeric',
            'option_groups.*.items.*.is_default' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request) {
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
            ]);

            foreach ($request->option_groups ?? [] as $groupIndex => $groupData) {
                $group = $menu->optionGroups()->create([
                    'label'       => $groupData['label'],
                    'input_type'  => $groupData['input_type'],
                    'is_required' => $groupData['is_required'] ?? false,
                    'min_select'  => $groupData['min_select'] ?? 0,
                    'max_select'  => $groupData['max_select'] ?? null,
                    'placeholder' => $groupData['placeholder'] ?? null,
                    'min_value'   => $groupData['min_value'] ?? null,
                    'max_value'   => $groupData['max_value'] ?? null,
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
     * Display the specified resource.
     */

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

        // kalau option_groups dikirim dari FormData sebagai JSON string
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
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',

            'option_groups' => 'sometimes|array',
            'option_groups.*.label' => 'required|string|max:255',
            'option_groups.*.input_type' => 'required|in:radio,checkbox,select,switch,number,text,textarea',
            'option_groups.*.is_required' => 'nullable|boolean',
            'option_groups.*.min_select' => 'nullable|integer|min:0',
            'option_groups.*.max_select' => 'nullable|integer|min:0',
            'option_groups.*.placeholder' => 'nullable|string|max:255',
            'option_groups.*.min_value' => 'nullable|integer',
            'option_groups.*.max_value' => 'nullable|integer',

            'option_groups.*.items' => 'nullable|array',
            'option_groups.*.items.*.label' => 'required|string|max:255',
            'option_groups.*.items.*.value' => 'nullable|string|max:255',
            'option_groups.*.items.*.price_adjustment' => 'nullable|numeric',
            'option_groups.*.items.*.is_default' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($request, $menu) {
            $data = $request->only([
                'name',
                'description',
                'price',
                'category',
                'rating',
            ]);

            if ($request->hasFile('image')) {
                if ($menu->image) {
                    Storage::disk('public')->delete($menu->image);
                }

                $data['image'] = $request->file('image')->store('menus', 'public');
            }

            $menu->update($data);

            // kalau frontend mengirim option_groups, replace semua opsi lama
            if ($request->exists('option_groups')) {
                // hapus group lama -> item ikut terhapus karena cascade
                $menu->optionGroups()->delete();

                foreach ($request->option_groups ?? [] as $groupIndex => $groupData) {
                    $group = $menu->optionGroups()->create([
                        'label'       => $groupData['label'],
                        'input_type'  => $groupData['input_type'],
                        'is_required' => $groupData['is_required'] ?? false,
                        'min_select'  => $groupData['min_select'] ?? 0,
                        'max_select'  => $groupData['max_select'] ?? null,
                        'placeholder' => $groupData['placeholder'] ?? null,
                        'min_value'   => $groupData['min_value'] ?? null,
                        'max_value'   => $groupData['max_value'] ?? null,
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
                ->with('optionGroups.items')
                ->latest()
                ->get()
        );
    }
}
