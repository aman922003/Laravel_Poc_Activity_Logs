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
use App\Repositories\UserRepositoryInterface;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return response()->json($this->userRepository->all());
    }

    public function show($id)
    {
        $user = $this->userRepository->find($id);
        return $user ? response()->json($user) : response()->json(['error' => 'Not found'], 404);
    }

    public function store(StoreUser $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

   public function update(UpdateUser $request, $id)
    {
        dd($request->validated());
        try 
        {
            DB::beginTransaction();
            $user = $this->userRepository->find($id);
            if (! $user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $data = $request->only(['name', 'email', 'password', 'contact_number', 'address']);
            $updatedUser = $this->userRepository->update($user, $data);
            DB::commit();
            return response()->json([
                'message' => 'User updated successfully',
                'user'    => $updatedUser
            ], 200);

        } catch (\Throwable $t) {
            DB::rollBack();

            Log::error("Exception in User Update: " . $t->getMessage(), [
                'trace' => $t->getTraceAsString()
            ]);

            return response()->json([
                'error'   => 'Something went wrong while updating user',
                'message' => $t->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        $user = $this->userRepository->find($id);
        if (! $user) return response()->json(['error' => 'Not found'], 404);

        $this->userRepository->softDelete($user);
        return response()->json(['message' => 'User soft deleted']);
    }

    public function restore($id)
    {
        $user = $this->userRepository->restore($id);
        return $user ? response()->json(['message' => 'User restored', 'user' => $user]) 
                     : response()->json(['error' => 'Not found or not deleted'], 404);
    }

    public function forceDelete($id)
    {
        $deleted = $this->userRepository->forceDelete($id);
        return $deleted ? response()->json(['message' => 'User permanently deleted'])
                        : response()->json(['error' => 'Not found'], 404);
    }
}
