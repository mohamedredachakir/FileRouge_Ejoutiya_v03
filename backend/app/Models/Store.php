<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'store_name',
    'bio',
    'logo',
    'hero_image',
    'status'
])]
class Store extends Model
{
    protected $appends = ['logo_url', 'hero_image_url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->toPublicImageUrl($this->logo);
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->toPublicImageUrl($this->hero_image);
    }

    private function toPublicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return url(ltrim($path, '/'));
        }

        $base = rtrim((string) config('filesystems.disks.public.url', url('/storage')), '/');

        return $base . '/' . ltrim($path, '/');
    }
}
