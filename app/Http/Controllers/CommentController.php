<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    /**
     * Get all comments with associated posts
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $auth = Auth::user();

            $search = $request->query('search');
            $name = $request->query('name');
            $email = $request->query('email');
            $postId = $request->query('post_id');
            $perPage = (int) $request->query('per_page', 10);
            $page = (int) $request->query('page', 1);

            $sortBy = $request->query('sort_by', 'id');
            $sortOrder = strtolower($request->query('sort_order', 'desc'));

            $allowedSortFields = ['id', 'name', 'email', 'created_at', 'updated_at'];
            $allowedSortOrders = ['asc', 'desc'];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'id';
            }
            if (!in_array($sortOrder, $allowedSortOrders)) {
                $sortOrder = 'desc';
            }

            $query = Comment::query()
                ->where('user_id', $auth->id)
                ->with('post');

            if ($name) {
                $query->where('name', $name);
            } elseif ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('content', 'LIKE', '%' . $search . '%');
                });
            }

            if ($email) {
                $query->where('email', $email);
            }
            if ($postId) {
                $query->where('post_id', $postId);
            }

            $query->orderBy($sortBy, $sortOrder);

            $comments = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedComments = collect($comments->items())->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'uuid' => $comment->uuid,
                    'post_id' => $comment->post_id,
                    'name' => $comment->name,
                    'email' => $comment->email,
                    'phone' => $comment->phone,
                    'media' => $comment->media,
                    'content' => $comment->content,
                    'post' => $comment->post,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ];
            });

            $links = [
                'first' => $comments->url(1),
                'last' => $comments->url($comments->lastPage()),
                'prev' => $comments->previousPageUrl(),
                'next' => $comments->nextPageUrl(),
            ];

            $pageLinks = [];
            for ($i = 1; $i <= $comments->lastPage(); $i++) {
                $pageLinks[] = [
                    'url' => $comments->url($i),
                    'label' => $i,
                    'active' => $i == $comments->currentPage(),
                ];
            }

            return response()->json([
                'message' => 'List Comments',
                'data' => $formattedComments,
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'per_page' => $comments->perPage(),
                    'total' => $comments->total(),
                    'last_page' => $comments->lastPage(),
                    'from' => $comments->firstItem(),
                    'to' => $comments->lastItem(),
                    'total_pages' => $comments->lastPage(),
                    'has_more_pages' => $comments->hasMorePages(),
                    'has_previous_pages' => $comments->currentPage() > 1,
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
                    'email' => $email,
                    'search' => $search,
                    'post_id' => $postId,
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
        try {
            $auth = Auth::user();
            $validated = $request->validate([
                'post_id' => 'required|integer|exists:posts,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'nullable|string|max:50',
                'media' => 'nullable|string',
                'content' => 'required|string',
            ]);

            $comment = Comment::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $auth->id,
                'post_id' => $validated['post_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'media' => $validated['media'] ?? null,
                'content' => $validated['content'],
            ]);

            return response()->json([
                'message' => 'Comment created successfully',
                'data' => $comment,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('user_id', $auth->id)
                ->with('post')
                ->find($id);
            if (!$comment) {
                return response()->json([
                    'message' => 'Comment not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Comment fetched successfully',
                'data' => $comment,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('user_id', $auth->id)->find($id);
            if (!$comment) {
                return response()->json([
                    'message' => 'Comment not found',
                ], 404);
            }

            $validated = $request->validate([
                'post_id' => 'sometimes|required|integer|exists:posts,id',
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email',
                'phone' => 'nullable|string|max:50',
                'media' => 'nullable|string',
                'content' => 'sometimes|required|string',
            ]);

            if (array_key_exists('post_id', $validated)) {
                $comment->post_id = $validated['post_id'];
            }
            if (array_key_exists('name', $validated)) {
                $comment->name = $validated['name'];
            }
            if (array_key_exists('email', $validated)) {
                $comment->email = $validated['email'];
            }
            if (array_key_exists('phone', $validated)) {
                $comment->phone = $validated['phone'];
            }
            if (array_key_exists('media', $validated)) {
                $comment->media = $validated['media'];
            }
            if (array_key_exists('content', $validated)) {
                $comment->content = $validated['content'];
            }

            $comment->save();

            return response()->json([
                'message' => 'Comment updated successfully',
                'data' => $comment,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('user_id', $auth->id)->find($id);
            if (!$comment) {
                return response()->json([
                    'message' => 'Comment not found',
                ], 404);
            }
            $comment->delete();
            return response()->json([
                'message' => 'Comment deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }
}
