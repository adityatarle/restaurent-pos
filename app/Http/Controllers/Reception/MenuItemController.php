<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For file uploads

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category')->orderBy('name');
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $menuItems = $query->paginate(10);
        $categories = Category::orderBy('name')->get();
        return view('reception.menu_items.index', compact('menuItems', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('reception.menu_items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation
        ]);

        $data = $request->except('image'); // Get all data except image for now
        $data['is_available'] = $request->has('is_available'); // Set boolean correctly

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menu_images', 'public'); // Stores in storage/app/public/menu_images
            $data['image_path'] = $path;
        }

        MenuItem::create($data);

        return redirect()->route('reception.menu-items.index')
                         ->with('success', 'Menu item created successfully.');
    }

    public function show(MenuItem $menuItem) // Route model binding
    {
        $menuItem->load('category');
        return view('reception.menu_items.show', compact('menuItem'));
    }

    public function edit(MenuItem $menuItem) // Route model binding
    {
        $categories = Category::orderBy('name')->get();
        return view('reception.menu_items.edit', compact('menuItem', 'categories'));
    }

    public function update(Request $request, MenuItem $menuItem) // Route model binding
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->except('image');
        $data['is_available'] = $request->has('is_available');

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $path = $request->file('image')->store('menu_images', 'public');
            $data['image_path'] = $path;
        }

        $menuItem->update($data);

        return redirect()->route('reception.menu-items.index')
                         ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem) // Route model binding
    {
        // Optional: Check if item is in any active orders before deleting
        // This can get complex, for now, simple delete
        if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
            Storage::disk('public')->delete($menuItem->image_path);
        }
        $menuItem->delete();
        return redirect()->route('reception.menu-items.index')
                         ->with('success', 'Menu item deleted successfully.');
    }
}