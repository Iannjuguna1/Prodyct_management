<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    /** @test */
    public function it_can_create_a_new_product()
    {
        $data = [
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 99.99,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('products', $data);
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $product->id,
                     'name' => $product->name,
                     'description' => $product->description,
                     'price' => (string) $product->price, // Cast price to string
                 ]);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user); // Authenticate as the owner

        $data = [
            'name' => 'Updated Product Name',
            'price' => 199.99,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('products', $data);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user); // Authenticate as the owner

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'Phone']);
        Product::factory()->create(['name' => 'Laptop']);

        $response = $this->getJson('/api/product/search?name=Phone');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['name' => 'Phone']);
    }

    /** @test */
    public function it_restricts_updating_a_product_to_the_owner()
    {
        $product = Product::factory()->create(['user_id' => 1]);
        $this->actingAs(User::factory()->create(['id' => 2]));

        $response = $this->putJson("/api/products/{$product->id}", ['name' => 'Updated Name']);

        $response->assertStatus(403); // Forbidden
    }
}
