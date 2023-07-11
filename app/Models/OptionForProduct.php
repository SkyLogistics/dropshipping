<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class OptionForProduct extends Model
{
    protected $table = 'option_for_product';
    protected $fillable = [
        'product_id',
        'option_id',
        'value'
    ];
}
