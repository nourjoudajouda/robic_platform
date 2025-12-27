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
            'name_en'  => 'required',
            'name_ar'  => 'required',
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

        $oldValues = $id ? $category->only(['name_en', 'name_ar', 'karat', 'price']) : null;
        $category->name  = $request->name_en; // Keep for backward compatibility
        $category->name_en  = $request->name_en;
        $category->name_ar  = $request->name_ar;
        $category->karat = $request->karat;
        $category->price = $request->price;
        $category->save();

        if ($id) {
            $newValues = $category->only(['name_en', 'name_ar', 'karat', 'price']);
            $this->audit('update', 'تم تحديث الفئة: ' . $category->name_en, $category, $oldValues, $newValues);
        } else {
            $this->audit('create', 'تم إنشاء فئة جديدة: ' . $category->name_en, $category);
        }

        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $category = Category::findOrFail($id);
        $oldStatus = $category->status;
        $result = Category::changeStatus($id);
        $category->refresh();
        
        $statusText = $category->status == \App\Constants\Status::ENABLE ? 'تفعيل' : 'تعطيل';
        $this->audit('status_change', "تم {$statusText} الفئة: " . $category->name_en, $category, ['status' => $oldStatus], ['status' => $category->status]);
        
        return $result;
    }

}
