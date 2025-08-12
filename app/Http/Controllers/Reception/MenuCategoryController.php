<?php

namespace App\Http\Controllers\Reception; // Ensure this namespace is correct

use App\Http\Controllers\Controller;    // Make sure to use the base Controller
use App\Models\Category;
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(10);
        return view('reception.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('reception.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

        return redirect()->route('reception.categories.index')
                         ->with('success', 'Menu category created successfully.');
    }

    /**
     * Display the specified resource.
     */
   public function show(Category $category) // Using route model binding for Category
{
    // Eager load menu items or fetch them
    $menuItems = $category->menuItems()->orderBy('name')->get(); // or ->paginate(10)
    return view('reception.categories.show', compact('category', 'menuItems'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category) // Route model binding
    {
        return view('reception.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category) // Route model binding
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        return redirect()->route('reception.categories.index')
                         ->with('success', 'Menu category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category) // Route model binding
    {
        // Optional: Add check if category has menu items before deleting
        if ($category->menuItems()->exists()) {
            return back()->with('error', 'Cannot delete category. It has menu items associated with it. Please remove items first or reassign them.');
        }

        $category->delete();
        return redirect()->route('reception.categories.index')
                         ->with('success', 'Menu category deleted successfully.');
    }
}