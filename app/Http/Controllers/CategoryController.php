<?php

namespace App\Http\Controllers;

use App\Models\ClubCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(ClubCategory::all());
    }

    public function show($id)
    {
        $category = ClubCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $category = ClubCategory::create([
            'name' => $request->name,
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = ClubCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = ClubCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
