<?php

namespace App\Http\Controllers;

use App\Models\CategoriPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $categoriPosts = CategoriPost::where('user_id', $auth->id)->get();
        return response()->json([
            'success' => true,
            'data' => $categoriPosts,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
