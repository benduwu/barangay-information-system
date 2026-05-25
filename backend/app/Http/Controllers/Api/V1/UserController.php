<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a paginated list of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->role($request->role); // Spatie scope
        }

        // Status filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate($request->input('per_page', 10));

        return response()->json([
            'users' => UserResource::collection($users),
            'meta'  => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'username'  => $request->username,
            'email'     => $request->email,
            'full_name' => $request->full_name,
            'password'  => $request->password,
            'is_active' => true,
        ]);

        // Assign Spatie role
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->only(['username', 'email', 'full_name', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        // Update Spatie role if changed
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Deactivate (soft delete) the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 403);
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete(); // Revoke all tokens
        $user->delete(); // Soft delete

        return response()->json([
            'message' => 'User deactivated successfully.',
        ]);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        $newPassword = $request->input('password', Str::random(12));

        $user->update([
            'password'             => $newPassword,
            'password_reset_token' => null,
        ]);

        // Revoke all existing tokens so user must re-login
        $user->tokens()->delete();

        return response()->json([
            'message'      => 'Password reset successfully.',
            'new_password' => $request->has('password') ? null : $newPassword,
        ]);
    }
}
