<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get paginated users including soft deleted ones.
     */
    public function all($perPage = 15)
    {
        return User::withTrashed()->paginate($perPage);
    }

    public function find($id): ?User
    {
        return User::withTrashed()->find($id);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = auth()->check() ? auth()->id() : null;
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['updated_by'] = auth()->check() ? auth()->id() : null;
        $user->update($data);
        return $user;
    }

    public function softDelete(User $user): bool
    {
        return $user->delete();
    }

    public function restore($id): ?User
    {
        $user = User::withTrashed()->find($id);
        if ($user && $user->trashed()) {
            $user->restore();
        }
        return $user;
    }

    public function forceDelete($id): bool
    {
        $user = User::withTrashed()->find($id);
        return $user ? $user->forceDelete() : false;
    }
}
