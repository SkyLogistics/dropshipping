<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrigamiProducts extends Model
{
    protected $table = 'origami_product';

    protected $fillable = [
        'vendorCode',
        'imageUrl',
        'promID',
        'quantityInStock',
        'nameUa',
        'vendor',
        'description',
        'description_ua',
        'productType',
        'size',
        'price',
        'recommendedPrice',
        'hasHigherPrice',
        'active',
    ];

}
