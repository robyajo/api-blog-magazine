<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *  * @return JsonResponse
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get query parameters
            $search = $request->query('search');
            $name = $request->query('name'); // Exact name parameter
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $role = $request->query('role');

            // Ordering parameters
            $sortBy = $request->query('sort_by', 'id'); // Default sort by id
            $sortOrder = $request->query('sort_order', 'desc'); // Default desc

            // Validate sort parameters
            $allowedSortFields = ['id', 'name', 'email', 'created_at', 'updated_at'];
            $allowedSortOrders = ['asc', 'desc'];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'id';
            }

            if (!in_array(strtolower($sortOrder), $allowedSortOrders)) {
                $sortOrder = 'desc';
            }

            // Build query
            $query = User::query();

            // Exact search by name if provided (priority over general search)
            if ($name) {
                // Use exact match for name parameter
                $query->where('name', $name);
            }
            // General search by name or email if provided (partial match)
            elseif ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }
            // Filter by role if provided
            if ($role) {
                $query->where('role', $role);
            }

            // Apply ordering
            $query->orderBy($sortBy, $sortOrder);

            // Paginate with custom page
            $users = $query->paginate($perPage, ['*'], 'page', $page);

            // Format users data
            $formattedUsers = collect($users->items())->map(function ($user) {
                return $this->formatUserResponse($user);
            });



            if ($name) {
                $activityProperties['name'] = $name;
            }
            if ($search) {
                $activityProperties['search'] = $search;
            }

            if ($role) {
                $activityProperties['role'] = $role;
            }
            if ($perPage != 10) {
                $activityProperties['per_page'] = $perPage;
            }
            if ($page != 1) {
                $activityProperties['page'] = $page;
            }
            if ($sortBy != 'id' || $sortOrder != 'desc') {
                $activityProperties['sort_by'] = $sortBy;
                $activityProperties['sort_order'] = $sortOrder;
            }


            // Build links for pagination
            $links = [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ];

            // Build page links (for numbered pagination)
            $pageLinks = [];
            for ($i = 1; $i <= $users->lastPage(); $i++) {
                $pageLinks[] = [
                    'url' => $users->url($i),
                    'label' => $i,
                    'active' => $i == $users->currentPage(),
                ];
            }

            return response()->json([
                'message' => 'List User',
                'data' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                    'total_pages' => $users->lastPage(),
                    'has_more_pages' => $users->hasMorePages(),
                    'has_previous_pages' => $users->currentPage() > 1,
                ],
                'links' => $links,
                'page_links' => $pageLinks,
                'sorting' => [
                    'current_sort_by' => $sortBy,
                    'current_sort_order' => $sortOrder,
                    'available_sort_fields' => $allowedSortFields,
                ],
                'filters' => [
                    'name' => $name,
                    'search' => $search,
                    'role' => $role,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }
        $validated = $validator->validated();

        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);


        return response()->json([
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        return response()->json([
            'message' => 'User fetched successfully',
            'data' => $user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,user',
            'avatar' => 'nullable|string',
            'avatar_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (array_key_exists('role', $validated)) {
            $user->role = $validated['role'];
        }
        if (array_key_exists('avatar', $validated)) {
            $user->avatar = $validated['avatar'];
        }
        if (array_key_exists('avatar_url', $validated)) {
            $user->avatar_url = $validated['avatar_url'];
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }

    private function formatUserResponse($user)
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
