<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryItemController extends Controller
{
    public function index($id)
    {
        $query = Item::with('images', 'tags')
            ->withCount('tradeRequests')
            ->whereIn('status', ['requested', 'available'])
            ->where('category_id', $id);

        if (auth()->check()) {
            $query->whereNot('user_id', auth()->id());
        }

        $items = $query->get();

        return Inertia::render('Item/ItemPerCategory', [
            'items' => $items,
            'items_count' => $items->count(),
        ]);
    }

    public function show($id)
    {
        $query = Item::with('images', 'tags', 'user')
            ->withCount('tradeRequests')
            ->where('category_id', $id);

        $items = $query->get();
        $category = Category::findOrFail($id);

        return Inertia::render('Moderator/Category/ViewItemPerCategory', [
            'items' => $items,
            'items_count' => $items->count(),
            'category' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|unique:categories|max:255',
            'description' => 'required|string|max:1000',
            'category_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ], [
            'category.unique' => 'This category name already exists.',
            'description.min' => 'The description must be at least 10 characters.',
            'category_image.dimensions' => 'The image must be at least 200x200 pixels.',
        ]);

        $category = new Category;
        $category->category = $request->category;
        $category->description = $request->description;

        if ($request->hasFile('category_image')) {
            $path = $request->file('category_image')->store('category-images', 'public');
            $category->category_image = $path;
        }

        $category->save();

        return redirect()->back()->with('success', 'Category created successfully');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        $category->category_image_url = $category->category_image
            ? Storage::disk('public')->url($category->category_image)
            : null;

        return Inertia::render('Moderator/Category/EditCategoryForm', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => [
                'required',
                'string',
                'max:255',
                'unique:categories,category,'.$id,
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
            'category_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:1024',
                'dimensions:min_width=200,min_height=200,max_width=2000,max_height=2000',
            ],
        ], [
            'category.unique' => 'This category name already exists.',
            'category_image.dimensions' => 'The image must be between 200x200 and 2000x2000 pixels.',
            'category_image.max' => 'The image must not be larger than 1MB.',
        ]);

        $category = Category::findOrFail($id);

        $category->category = $request->category;
        $category->description = $request->description;

        if ($request->hasFile('category_image')) {
            if ($category->category_image && Storage::disk('public')->exists($category->category_image)) {
                Storage::disk('public')->delete($category->category_image);
            }

            $path = $request->file('category_image')->store('category-images', 'public');
            $category->category_image = $path;
        }

        $category->save();

        return redirect('/moderator/category')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has related items
            if ($category->items()->count() > 0) {
                return back()->with('error', 'Cannot delete category: It has associated items');
            }

            if ($category->category_image && Storage::disk('public')->exists($category->category_image)) {
                Storage::disk('public')->delete($category->category_image);
            }

            $category->delete();

            return redirect()->back()->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete category: '.$e->getMessage());
        }
    }
}
