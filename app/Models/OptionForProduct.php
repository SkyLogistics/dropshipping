<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionForProduct extends Model
{
    protected $table = 'option_for_product';

    public $fillable = ['value', 'product_id', 'option_id'];

}
