<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $appends = ['image_url'];

    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        if (Str::startsWith($this->image_path, ['/storage/', 'storage/'])) {
            return url(ltrim($this->image_path, '/'));
        }

        $base = rtrim((string) config('filesystems.disks.public.url', url('/storage')), '/');

        return $base . '/' . ltrim($this->image_path, '/');
    }
}
