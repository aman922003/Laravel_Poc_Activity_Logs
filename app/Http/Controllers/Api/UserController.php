<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use App\Services\ActivityLogger;
use App\Repositories\UserRepositoryInterface;

class UserController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ActivityLogger
     */
    private $logger;

    /**
     * UserController constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository , ActivityLogger $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    /**
     * Display a listing of all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return handleTransaction(function () {
            $users = $this->userRepository->all();

            $this->logger->log('Viewed user list', null, ['count' => count($users)]);

            return ['users' => $users];
        });
    }

    /**
     * Display a specific user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];

        $this->logger->log('Viewed user details', $user, ['id' => $id, 'name' => $user->name]);

            return ['user' => $user];
        });
    }

    /**
     * Store a newly created user.
     *
     * @param StoreUser $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUser $request)
    {
        return handleTransaction(function () use ($request) {
            $data = $request->validated();
            // $data['password'] = Hash::make($data['password']);
            $user = $this->userRepository->create($data);

            $this->logger->log('User created', $user);

            return ['message' => 'User created successfully', 'user' => $user, 'status' => 201];
        });
    }

    /**
     * Update the specified user by ID.
     *
     * @param UpdateUser $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
//    public function update(UpdateUser $request, $id)
//     {
//         return handleTransaction(function () use ($request, $id) {
//             $user = $this->userRepository->find($id);
//             if (! $user) return ['error' => 'User not found', 'status' => 404];

//             $data = $request->only(['name', 'email', 'password', 'contact_number', 'address']);
//             if (isset($data['password'])) {
//                 $data['password'] = Hash::make($data['password']);
//             }

//             $updatedUser = $this->userRepository->update($user, $data);

//         activity()
//             ->causedBy(auth()->user())
//             ->performedOn($updatedUser)
//             ->withProperties([
//                 'old' => $original,
//                 'changes' => $updatedUser->getChanges()
//             ])
//             ->log('Updated user');

//             return ['message' => 'User updated successfully', 'user' => $updatedUser];
//         });
//     }
        public function update(UpdateUser $request, $id)
    {
        return handleTransaction(function () use ($request, $id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];

            $original = $user->getOriginal();
            $data = $request->only(['name', 'email', 'password', 'contact_number', 'address']);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $updatedUser = $this->userRepository->update($user, $data);

            // $this->logger->log('User updated', $updatedUser, [
            //     'old' => $original,
            //     'changes' => $updatedUser->getChanges(),
            // ]);
            $this->logger->logUserUpdate(
                $updatedUser,
                $original,
                $updatedUser->getChanges()
            );


            return ['message' => 'User updated successfully', 'user' => $updatedUser];
        });
    }


    /**
     * Soft delete a user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) return ['error' => 'User not found', 'status' => 404];

            // Prevent user from deleting themselves
            if ($user->id === auth()->id()) {
                return ['error' => 'You cannot delete your own account', 'status' => 403];
            }

            $this->userRepository->softDelete($user);

            $this->logger->log('User soft deleted', $user);

            return ['message' => 'User soft deleted'];
        });
    }

    /**
     * Restore a soft deleted user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
       return handleTransaction(function () use ($id) {
            $user = $this->userRepository->restore($id);
            if (! $user) return ['error' => 'Not found or not deleted', 'status' => 404];

            $this->logger->log('User restored', $user);

            return ['message' => 'User restored', 'user' => $user];
        });
    }

    /**
     * Permanently delete a user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        return handleTransaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            if (! $user) {
                return ['error' => 'User not found', 'status' => 404];
            }

            // Prevent user from deleting themselves
            if ($user->id === auth()->id()) {
                return ['error' => 'You cannot delete your own account', 'status' => 403];
            }

            $this->userRepository->forceDelete($id);

            $this->logger->log('User permanently deleted', $user, ['id' => $id]);

            return ['message' => 'User permanently deleted'];
        });
    }
}
