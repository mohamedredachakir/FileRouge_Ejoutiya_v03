<?php

namespace App\Models;

use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'store_id',
    'name',
    'description',
    'price',
    'stock',
    'category',
    'status',
    'sizes'
])]
class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'sizes' => 'array',
    ];

    protected $appends = ['main_image_url'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getMainImageUrlAttribute(): ?string
    {
        $image = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $image?->image_url;
    }
}
