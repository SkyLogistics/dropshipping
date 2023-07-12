<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOption extends Model
{
    protected $table = 'product_option';
    protected $fillable = [
        'title',
        'lang',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(OrigamiProducts::class,'origami_product');
    }
}
