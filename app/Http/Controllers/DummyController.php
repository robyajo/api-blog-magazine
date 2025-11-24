<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Http;

class DummyController extends Controller
{
    /**
     * Fetch users from dummyjson.
     */
    public function dummyUsers()
    {
        return $this->fetchData('https://dummyjson.com/users');
    }

    /**
     * Fetch a single user by ID.
     */
    public function dummyUsersId($id)
    {
        return $this->fetchData("https://dummyjson.com/users/{$id}");
    }

    /**
     * Fetch products from dummyjson.
     */
    public function dummyProduct()
    {
        return $this->fetchData('https://dummyjson.com/products');
    }

    /**
     * Fetch a single product by ID.
     */
    public function dummyProductId($id)
    {
        return $this->fetchData("https://dummyjson.com/products/{$id}");
    }

    /**
     * Fetch posts from dummyjson.
     */
    public function dummyPosts()
    {
        return $this->fetchData('https://dummyjson.com/posts');
    }

    /**
     * Fetch a single post by ID.
     */
    public function dummyPostsId($id)
    {
        return $this->fetchData("https://dummyjson.com/posts/{$id}");
    }

    /**
     * Helper method to fetch data from external API.
     */
    private function fetchData(string $url)
    {
        try {
            $response = Http::get($url);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Failed to fetch data from external API',
                    'error' => $response->body(),
                ], $response->status());
            }

            return response()->json([
                'message' => 'Data fetched successfully',
                'data' => $response->json(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
