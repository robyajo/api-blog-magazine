<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Get all posts with associated categories
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $auth = Auth::user();

            $search = $request->query('search');
            $title = $request->query('title');
            $perPage = (int) $request->query('per_page', 10);
            $page = (int) $request->query('page', 1);
            $category = $request->query('category');

            $sortBy = $request->query('sort_by', 'id');
            $sortOrder = strtolower($request->query('sort_order', 'desc'));

            $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
            $allowedSortOrders = ['asc', 'desc'];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'id';
            }
            if (!in_array($sortOrder, $allowedSortOrders)) {
                $sortOrder = 'desc';
            }

            $query = Post::query()
                ->where('user_id', $auth->id)
                ->with('categori');

            if ($title) {
                $query->where('name', $title);
            } elseif ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('content', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%');
                });
            }

            if ($category) {
                $query->whereHas('categori', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            }

            $query->orderBy($sortBy, $sortOrder);

            $posts = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedPosts = collect($posts->items())->map(function ($post) {
                return [
                    'id' => $post->id,
                    'name' => $post->name ?? null,
                    'content' => $post->content ?? null,
                    'categori' => $post->categori ?? null,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ];
            });

            $links = [
                'first' => $posts->url(1),
                'last' => $posts->url($posts->lastPage()),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
            ];

            $pageLinks = [];
            for ($i = 1; $i <= $posts->lastPage(); $i++) {
                $pageLinks[] = [
                    'url' => $posts->url($i),
                    'label' => $i,
                    'active' => $i == $posts->currentPage(),
                ];
            }

            return response()->json([
                'message' => 'List Posts',
                'data' => $formattedPosts,
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'last_page' => $posts->lastPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                    'total_pages' => $posts->lastPage(),
                    'has_more_pages' => $posts->hasMorePages(),
                    'has_previous_pages' => $posts->currentPage() > 1,
                ],
                'links' => $links,
                'page_links' => $pageLinks,
                'sorting' => [
                    'current_sort_by' => $sortBy,
                    'current_sort_order' => $sortOrder,
                    'available_sort_fields' => $allowedSortFields,
                ],
                'filters' => [
                    'title' => $title,
                    'search' => $search,
                    'category' => $category,
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
                'name' => 'required|string|max:255',
                'content' => 'required|string',
                'description' => 'nullable|string',
                'categori_id' => 'nullable|integer|exists:categori_posts,id',
                'image' => 'nullable|string',
                'image_url' => 'nullable|string',
                'status' => 'nullable|in:draft,published,archived',
                'tags' => 'nullable|string',
            ]);

            $post = Post::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $auth->id,
                'categori_id' => $validated['categori_id'] ?? null,
                'name' => $validated['name'],
                'slug' => \Illuminate\Support\Str::slug($validated['name']),
                'image' => $validated['image'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'tags' => $validated['tags'] ?? null,
                'content' => $validated['content'],
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'message' => 'Post created successfully',
                'data' => $post,
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
            $post = Post::where('user_id', $auth->id)
                ->with('categori')
                ->find($id);
            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Post fetched successfully',
                'data' => $post,
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
            $post = Post::where('user_id', $auth->id)->find($id);
            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'description' => 'nullable|string',
                'categori_id' => 'nullable|integer|exists:categori_posts,id',
                'image' => 'nullable|string',
                'image_url' => 'nullable|string',
                'status' => 'nullable|in:draft,published,archived',
                'tags' => 'nullable|string',
            ]);

            if (array_key_exists('name', $validated)) {
                $post->name = $validated['name'];
                $post->slug = \Illuminate\Support\Str::slug($validated['name']);
            }
            if (array_key_exists('content', $validated)) {
                $post->content = $validated['content'];
            }
            if (array_key_exists('description', $validated)) {
                $post->description = $validated['description'];
            }
            if (array_key_exists('categori_id', $validated)) {
                $post->categori_id = $validated['categori_id'];
            }
            if (array_key_exists('image', $validated)) {
                $post->image = $validated['image'];
            }
            if (array_key_exists('image_url', $validated)) {
                $post->image_url = $validated['image_url'];
            }
            if (array_key_exists('status', $validated)) {
                $post->status = $validated['status'];
            }
            if (array_key_exists('tags', $validated)) {
                $post->tags = $validated['tags'];
            }

            $post->save();

            return response()->json([
                'message' => 'Post updated successfully',
                'data' => $post,
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
            $post = Post::where('user_id', $auth->id)->find($id);
            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                ], 404);
            }
            $post->delete();
            return response()->json([
                'message' => 'Post deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: ' . $th->getMessage(),
            ], 500);
        }
    }
}
