<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'title',
        'title_ua',
        'slug',
        'summary',
        'description',
        'description_ua',
        'cat_id',
        'child_cat_id',
        'price',
        'recommendedPrice',
        'brand_id',
        'discount',
        'status',
        'photo',
        'size',
        'stock',
        'is_featured',
        'condition',
        'vendor',
        'vendorCode',
        'options',
        'imageUrl',
        'quantityInStock',
        'keywords',
        'keywords_ua',
        'active',
    ];

    public function catInfo(): HasOne
    {
        return $this->hasOne('App\Models\Category', 'id', 'cat_id');
    }

    public function subCatInfo(): HasOne
    {
        return $this->hasOne('App\Models\Category', 'id', 'child_cat_id');
    }

    public static function getAllProduct(): LengthAwarePaginator
    {
        return Product::with(['catInfo', 'subCatInfo'])->orderBy('id', 'desc')->paginate(10);
    }

    public function relProducts(): HasMany
    {
        return $this->hasMany('App\Models\Product', 'cat_id', 'cat_id')->where('status', 'active')->orderBy(
            'id',
            'DESC'
        )->limit(8);
    }

    public function getReview(): HasMany
    {
        return $this->hasMany('App\Models\ProductReview', 'product_id', 'id')->with('user_info')->where(
            'status',
            'active'
        )->orderBy('id', 'DESC');
    }

    public static function getProductBySlug($slug): Builder|null
    {
        return Product::with(['catInfo', 'relProducts', 'getReview'])->where('slug', $slug)->first();
    }

    public static function countActiveProduct(): int
    {
        $data = Product::query()->where('status', 'active')->count();
        if ($data) {
            return $data;
        }
        return 0;
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class)->whereNotNull('order_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class)->whereNotNull('cart_id');
    }

    public function brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }
}
