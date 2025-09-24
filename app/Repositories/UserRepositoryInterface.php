<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function all();
    public function find($id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function softDelete(User $user): bool;
    public function restore($id): ?User;
    public function forceDelete($id): bool;
}
