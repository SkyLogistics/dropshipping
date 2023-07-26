<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Category extends Model
{
    protected $fillable = [
        'title',
        'title_ua',
        'slug',
        'summary',
        'photo',
        'status',
        'is_parent',
        'parent_id',
        'added_by',
        'cat_id'];

    public function parentInfo(): HasOne
    {
        return $this->hasOne('App\Models\Category', 'id', 'parent_id');
    }

    public static function getAllCategory(): LengthAwarePaginator
    {
        return Category::query()->orderBy('id', 'DESC')->with('parentInfo')->paginate(10);
    }

    public static function shiftChild($cat_id): int
    {
        return Category::query()->whereIn('id', $cat_id)->update(['is_parent' => 1]);
    }

    public static function getChildByParentID($id): Collection
    {
        return Category::query()->where('parent_id', $id)->orderBy('id', 'ASC')->pluck('title', 'id');
    }

    public function childCat(): HasMany
    {
        return $this->hasMany('App\Models\Category', 'parent_id', 'id')->where('status', 'active');
    }

    public static function getAllParentWithChild(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Category::with('childCat')->where('is_parent', 1)->where('status', 'active')->orderBy(
            'title_ua',
            'ASC'
        )->get();
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'cat_id', 'id')->where('status', 'active');
    }

    public function subProducts(): HasMany
    {
        return $this->hasMany('App\Models\Product', 'child_cat_id', 'id')->where('status', 'active');
    }

    public static function getProductByCat($slug)
    {
        return Category::with('products')->where('slug', $slug)->first();
    }

    public static function getProductBySubCat($slug)
    {
        return Category::with('subProducts')->where('slug', $slug)->first();
    }

    public static function countActiveCategory(): int
    {
        return intval(Category::query()->where('status', 'active')->count());
    }
}
