<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles', 'department.faculty', 'profileImage')
            ->hierarchical($request->user(), 'id');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        if ($request->has('role') && $request->role != '') {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $user = $this->userService->register($request->validated());
        
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('roles.permissions', 'department.faculty', 'expertise', 'profileImage'));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $user->update($request->validated());

        if ($request->has('roles') && $request->user()->isAdmin()) {
            $user->roles()->sync($request->roles);
        }

        return response()->json($user->load('roles'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(null, 204);
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $query = User::with('department', 'expertise')
            ->whereHas('roles', fn($q) => $q->where('name', 'researcher'))
            ->where('is_active', true);

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('expertise', fn($e) => $e->where('name', 'like', '%' . $request->search . '%'));
        }

        return response()->json($query->limit(50)->get());
    }

    public function publicShow(User $user): JsonResponse
    {
        if (!$user->is_active) {
            abort(404);
        }
        return response()->json($user->load('department.faculty.campus.university', 'expertise', 'profileImage', 'publications'));
    }
}
