<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateUser;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Paginated users list
     */
    public function index(Request $request)
    {
        return handleTransaction(function () use ($request) {
            $perPage = (int) $request->get('per_page', 50);
            $users = $this->userRepository->all($perPage);
            return ['users' => $users];
        });
    }

    public function show($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];
            return ['user' => $user];
        });
    }

    public function store(StoreUser $request)
    {
        return handleTransaction(function () use ($request) {
            $data = $request->validated();
            $user = $this->userRepository->create($data);
            return ['message' => 'User created successfully', 'user' => $user, 'status' => 201];
        });
    }

    public function update(UpdateUser $request, $id)
    {
        return handleTransaction(function () use ($request, $id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];
            $data = $request->only(['name', 'email', 'password', 'contact_number', 'address']);
            $updatedUser = $this->userRepository->update($user, $data);
            return ['message' => 'User updated successfully', 'user' => $updatedUser];
        });
    }

    public function destroy($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];
            if ($user->id === auth()->id()) {
                return ['error' => 'You cannot delete your own account', 'status' => 403];
            }
            $this->userRepository->softDelete($user);
            return ['message' => 'User soft deleted'];
        });
    }

    public function restore($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->restore($id);
            if (! $user) return ['error' => 'Not found or not deleted', 'status' => 404];
            return ['message' => 'User restored', 'user' => $user];
        });
    }

    public function forceDelete($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];
            if ($user->id === auth()->id()) {
                return ['error' => 'You cannot delete your own account', 'status' => 403];
            }
            $this->userRepository->forceDelete($id);
            return ['message' => 'User permanently deleted'];
        });
    }
}
