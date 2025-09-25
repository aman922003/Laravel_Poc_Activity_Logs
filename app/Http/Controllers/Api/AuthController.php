<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Services\ActivityLogger;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @var ActivityLogger
     */
    private $logger;

    /**
     * AuthController constructor.
     */
    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register a new user
     */
    public function register(RegisterUserRequest $request)
    {
        return handleTransaction(function () use ($request) {
            $user = User::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'contact_number' => $request->contact_number,
                'address'        => $request->address,
                'created_by'     => auth()->check() ? auth()->id() : null,
                'updated_by'     => null,
            ]);

             $this->logger->log('User registered', $user, ['registered_via' => 'api'], $user);

            return [
                'message' => 'User registered successfully',
                'user'    => $user,
                'status'  => 201
            ];
        });
    }

    /**
     * Login user and create Sanctum token
     */
    public function login(LoginUserRequest $request)
    {
        return handleTransaction(function () use ($request) {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return ['error' => 'Invalid credentials', 'status' => 401];
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $this->logger->log('User logged in', $user, ['token' => 'created'], $user);

            return [
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => $user
            ];
        });
    }

    /**
     * Logout user (delete tokens)
     */
    public function logout(Request $request)
    {
        return handleTransaction(function () use ($request) {
            $user = $request->user();
            $user->tokens()->delete();

            $this->logger->log('User logged out', $user, [], $user);

            return ['message' => 'Logged out successfully'];
        });
    }
}
