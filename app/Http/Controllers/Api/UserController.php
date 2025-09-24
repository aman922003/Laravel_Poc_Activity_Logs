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
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => $data])
            ->log('created user');

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

   public function update(UpdateUser $request, $id)
    {
        try 
        {
            DB::beginTransaction();
            $user = $this->userRepository->find($id);
            if (! $user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $data = $request->only(['name', 'email', 'password', 'contact_number', 'address']);
            $updatedUser = $this->userRepository->update($user, $data);
            
            activity()
                ->causedBy(auth()->user())
                ->performedOn($updatedUser)
                ->withProperties([
                    'old' => $oldData,
                    'attributes' => $data
                ])
                ->log('updated user');
            
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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('soft deleted user');

        $this->userRepository->softDelete($user);
        return response()->json(['message' => 'User soft deleted']);
    }

    public function restore($id)
    {
        $user = $this->userRepository->findTrashed($id);
        if (! $user) return response()->json(['error' => 'Not found or not deleted'], 404);

        $restoredUser = $this->userRepository->restore($user);

        // Activity log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($restoredUser)
            ->log('restored user');

        return response()->json([
            'message' => 'User restored successfully',
            'user'    => $restoredUser
        ]);
    }

    public function forceDelete($id)
    {
        $user = $this->userRepository->findTrashed($id);
        if (! $user) return response()->json(['error' => 'Not found'], 404);

        $this->userRepository->forceDelete($user);

        // Activity log
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('permanently deleted user');

        return response()->json(['message' => 'User permanently deleted']);
    }

    public function logs($id)
    {
        $user = $this->userRepository->findWithTrashed($id);
        if (! $user) return response()->json(['error' => 'User not found'], 404);

        $logs = \Spatie\Activitylog\Models\Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'action'       => $log->description,
                    'performed_by' => $log->causer ? $log->causer->name : 'System',
                    'changes'      => $log->properties,
                    'date'         => $log->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json($logs);
    }
}
