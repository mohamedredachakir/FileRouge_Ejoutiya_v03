<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends User
{
    use HasFactory;

    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $query) {
            $query->where('role', 'client');
        });

        static::creating(function (self $model) {
            $model->role = 'client';
        });
    }
}
