<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemSpec extends Model
{
    protected $guarded = ['id'];
    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }
    public function value()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'product_attribute_value_id');
    }
}