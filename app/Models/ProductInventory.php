<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_name',
        'stock',
        'harga_jual',
        'image_path',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'stock'      => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
