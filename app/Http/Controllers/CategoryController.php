<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            Category::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
            ]);
            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (QueryException $e) {
            return response()->json(['error' => 'A category with this name already exists'], 409);
        }
    }

    public function update(Request $request, Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== Auth::id()) {
            return redirect()->route('categories.index')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ], [
            'name.unique' => 'A category with this name already exists.',
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Ensure the category belongs to the authenticated user
        if ($category->user_id !== Auth::id()) {
            return redirect()->route('categories.index')->with('error', 'Unauthorized action.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
