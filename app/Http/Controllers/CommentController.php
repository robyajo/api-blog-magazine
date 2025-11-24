<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

            if (! in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'id';
            }
            if (! in_array($sortOrder, $allowedSortOrders)) {
                $sortOrder = 'desc';
            }

            $query = Comment::query()
                ->where('user_id', $auth->id)
                ->with('post');

            if ($name) {
                $query->where('name', $name);
            } elseif ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('email', 'LIKE', '%'.$search.'%')
                        ->orWhere('content', 'LIKE', '%'.$search.'%');
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
                return $this->formatResponse($comment);
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
                'message' => 'Server error: '.$th->getMessage(),
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
            $validator = Validator::make(
                $request->all(),
                [
                    'post_id' => 'required|integer|exists:posts,id',
                    'name' => 'required|string|max:255',
                    'email' => 'required|email',
                    'phone' => 'nullable|string|max:50',
                    'media' => 'nullable|string',
                    'content' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            $comment = Comment::create([
                'user_id' => $auth->id,
                'post_id' => $request->post_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? null,
                'media' => $request->media ?? null,
                'content' => $request->content,
            ]);

            return response()->json([
                'message' => 'Comment created successfully',
                'data' => $this->formatResponse($comment),
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('uuid', $uuid)
                ->with('post')
                ->first();
            if (! $comment) {
                return response()->json([
                    'message' => 'Comment not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Comment fetched successfully',
                'data' => $this->formatResponse($comment),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('uuid', $uuid)->first();
            if (! $comment) {
                return response()->json([
                    'message' => 'Comment not found',
                ], 404);
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'post_id' => 'sometimes|required|integer|exists:posts,id',
                    'name' => 'sometimes|required|string|max:255',
                    'email' => 'sometimes|required|email',
                    'phone' => 'nullable|string|max:50',
                    'media' => 'nullable|string',
                    'content' => 'sometimes|required|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }

            $comment->update([
                'post_id' => $request->post_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'media' => $request->media,
                'content' => $request->content,
            ]);

            return response()->json([
                'message' => 'Comment updated successfully',
                'data' => $this->formatResponse($comment),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: '.$th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        try {
            $auth = Auth::user();
            $comment = Comment::where('uuid', $uuid)->first();
            if (! $comment) {
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
                'message' => 'Server error: '.$th->getMessage(),
            ], 500);
        }
    }

    private function formatResponse($data)
    {

        return [
            'id' => $data->id,
            'uuid' => $data->uuid,
            'post_id' => $data->post_id,
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'media' => $data->media,
            'content' => $data->content,
            'post' => $data->post,
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
        ];
    }
}
