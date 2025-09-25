<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get all users including soft deleted ones.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return User::withTrashed()->get();
    }

    /**
     * Find a user by ID, including soft deleted users.
     *
     * @param int $id
     * @return User|null
     */
    public function find($id): ?User
    {
        return User::withTrashed()->find($id);
    }

    /**
     * Create a new user in the database.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        // $data['created_by'] = auth()->id();
        $data['created_by'] = auth()->check() ? auth()->user()->name : null;
        return User::create($data);
    }

    /**
     * Update an existing user with new data.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        // $data['updated_by'] = auth()->id();
        $data['updated_by'] = auth()->check() ? auth()->user()->name : null;
        $user->update($data);
        return $user;
    }

    /**
     * Soft delete a user (mark as deleted without removing from DB).
     *
     * @param User $user
     * @return bool
     */
    public function softDelete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Restore a soft deleted user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function restore($id): ?User
    {
        $user = User::withTrashed()->find($id);
        if ($user && $user->trashed()) {
            $user->restore();
        }
        return $user;
    }

    /**
     * Permanently delete a user from the database by ID.
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete($id): bool
    {
        $user = User::withTrashed()->find($id);
        return $user ? $user->forceDelete() : false;
    }
}
