<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Retrieve all users, including soft deleted ones.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();
    
    /**
     * Find a user by ID, including soft deleted users.
     *
     * @param int $id
     * @return User|null
     */
    public function find($id): ?User;
    
     /**
     * Create a new user in the database.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update an existing user's information.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User;

    /**
     * Soft delete a user (mark as deleted without removing from DB).
     *
     * @param User $user
     * @return bool
     */
    public function softDelete(User $user): bool;

    /**
     * Restore a soft deleted user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function restore($id): ?User;

    /**
     * Permanently delete a user from the database by ID.
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete($id): bool;
}
