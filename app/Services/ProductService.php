<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductService
{
    public function createProduct(Request $request): Model|Builder
    {
        $request->all();
        $slug = Str::slug($request->title);
        $count = Product::query()->where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;
        $data['is_featured'] = $request->input('is_featured', 0);
        $size = $request->input('size');
        if ($size) {
            $data['size'] = implode(',', $size);
        } else {
            $data['size'] = '';
        }

        return Product::query()->create($data);
    }
}
