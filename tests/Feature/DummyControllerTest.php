<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DummyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );
    }

    public function test_dummy_users_returns_successful_response()
    {
        Http::fake([
            'dummyjson.com/users' => Http::response(['users' => []], 200),
        ]);

        $response = $this->getJson('/api/dummy/users');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data fetched successfully',
                'data' => ['users' => []],
            ]);
    }

    public function test_dummy_users_id_returns_successful_response()
    {
        Http::fake([
            'dummyjson.com/users/1' => Http::response(['id' => 1, 'username' => 'test'], 200),
        ]);

        $response = $this->getJson('/api/dummy/users/1');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data fetched successfully',
                'data' => ['id' => 1, 'username' => 'test'],
            ]);
    }

    public function test_dummy_products_returns_successful_response()
    {
        Http::fake([
            'dummyjson.com/products' => Http::response(['products' => []], 200),
        ]);

        $response = $this->getJson('/api/dummy/product');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data fetched successfully',
                'data' => ['products' => []],
            ]);
    }

    public function test_dummy_products_id_returns_successful_response()
    {
        Http::fake([
            'dummyjson.com/products/1' => Http::response(['id' => 1, 'title' => 'test'], 200),
        ]);

        $response = $this->getJson('/api/dummy/product/1');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Data fetched successfully',
                'data' => ['id' => 1, 'title' => 'test'],
            ]);
    }

    public function test_dummy_api_failure_returns_error_response()
    {
        Http::fake([
            'dummyjson.com/*' => Http::response('Server Error', 500),
        ]);

        $response = $this->getJson('/api/dummy/users');

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Failed to fetch data from external API',
                'error' => 'Server Error',
            ]);
    }
}
