<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends User
{
    use HasFactory;

    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('role', function (Builder $query) {
            $query->where('role', 'admin');
        });

        static::creating(function (self $model) {
            $model->role = 'admin';
        });
    }

    public function banUser(int $userId): bool
    {
        return User::query()->whereKey($userId)->update(['is_banned' => true]) > 0;
    }

    public function unbanUser(int $userId): bool
    {
        return User::query()->whereKey($userId)->update(['is_banned' => false]) > 0;
    }
}
