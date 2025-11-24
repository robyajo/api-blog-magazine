<?php

namespace App\Http\Controllers;

use App\Models\CategoriPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoriController extends Controller
{
    /**
     * Get all categories with associated posts
     *
     * @return JsonResponse
     */
    public function index()
    {
        $auth = Auth::user();
        try {
            $categoriPosts = CategoriPost::where('user_id', $auth->id)->get();

            return response()->json([
                'message' => 'List Categories',
                'data' => $categoriPosts,
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
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'status' => 'nullable|in:active,inactive',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->toArray(),
                ], 422);
            }
            $category = CategoriPost::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'user_id' => $auth->id,
                'status' => $request->status,
                'description' => $request->description ?? null,
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $this->formatResponse($category),
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
    public function show(string $id)
    {
        try {
            $category = CategoriPost::where('uuid', $id)->first();
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Category fetched successfully',
                'data' => $this->formatResponse($category),
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
            $category = CategoriPost::where('uuid', $uuid)->first();
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }
            $validated = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|in:active,inactive',
            ]);
            if ($validated->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validated->errors()->toArray(),
                ], 422);
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $this->formatResponse($category),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Server error: '.$th->getMessage(),
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
            $category = CategoriPost::where('user_id', $auth->id)->find($id);
            if (! $category) {
                return response()->json([
                    'message' => 'Category not found',
                ], 404);
            }
            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully',
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
            'uuid' => $data->uuid,
            'user_id' => $data->user_id,
            'name' => $data->name,
            'slug' => $data->slug,
            'description' => $data->description,
            'status' => $data->status,
        ];
    }
}
