<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function list()
    {
        $pageTitle  = 'Categories';
        $categories = Category::withSum('assets as total_quantity', 'quantity')->orderBy('id', 'desc')->get();
        return view('admin.category.list', compact('pageTitle', 'categories'));
    }

    public function save(Request $request, $id = 0)
    {
        $request->validate([
            'name'  => 'required',
            'karat' => 'required|numeric|gt:0|lte:24',
            'price' => 'required|numeric|gt:0',
        ]);

        if ($id) {
            $category = Category::findOrFail($id);
            $notify[] = ['success', 'Category updated successfully'];
        } else {
            $category = new Category();
            $notify[] = ['success', 'Category added successfully'];
        }

        $category->name  = $request->name;
        $category->karat = $request->karat;
        $category->price = $request->price;
        $category->save();

        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Category::changeStatus($id);
    }

}
