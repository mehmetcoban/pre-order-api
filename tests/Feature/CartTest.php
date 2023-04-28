<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testCanShowCart()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/cart');

        $response->assertStatus(200);
    }

    public function testEmptyCartWithWrong()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/cart');

        $response->assertStatus(404);

        $response->assertJson(['message' => 'Basket not found']);
    }

    public function testCanAddToCart()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);
        $quantity = 2;

        $response = $this->actingAs($user)->postJson('/api/v1/cart', [
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Product added to cart successfully']);

        $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        $this->assertEquals($quantity, $cartItem->quantity);
    }

    public function testCanUpdateQuantity()
    {
        $quantity = 2;

        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $quantity
        ]);

        $response = $this->actingAs($user)->putJson('/api/v1/cart/' . $product->id, [
            'quantity' => $quantity
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Cart updated successfully']);
    }

    public function testCanRemoveFromCartItem()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/cart/' . $product->id);

        $response->assertStatus(204);
    }

    public function testRemoveFromCartItemNotInProduct()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/cart/' . $product->id);

        $response->assertStatus(404);

        $response->assertJson(['message' => 'Product not found in cart']);
    }

    public function testCanRemoveFromCart()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password')
        ]);
        $cart = Cart::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/cart');

        $response->assertStatus(204);
    }
}
