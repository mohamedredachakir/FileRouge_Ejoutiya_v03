<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreOwner extends User
{
    use HasFactory;

    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $query) {
            $query->where('role', 'store_owner');
        });

        static::creating(function (self $model) {
            $model->role = 'store_owner';
        });
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'user_id');
    }
}
