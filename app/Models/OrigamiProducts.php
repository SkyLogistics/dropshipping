<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrigamiProducts extends Model
{
    protected $table = 'origami_product';

    protected $fillable = [
        'options',
        'options_ua',
        'properties',
        'properties_ua',
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
        'provider',
        'productUrl',
    ];
}
