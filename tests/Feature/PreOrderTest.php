<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PreOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PreOrderTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $preOrder = PreOrder::factory()->create([
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'status' => 'waiting'
        ]);

        $response = $this->withoutMiddleware()->getJson('/api/v1/pre-order');

        $response->assertStatus(200);
    }

    public function testStoreWithValidDataShouldCreateNewPreOrder()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/pre-order', [
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => '+1' . $this->faker->numberBetween(201, 999) . $this->faker->numberBetween(1000000, 9999999),
            'status' => 'waiting'
        ]);

        $response->assertStatus(201);
    }

    public function testPhoneNumberValidationFailsWithWrongFormat()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/pre-order', [
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => '1234567890',
            'status' => 'waiting'
        ]);

        $response->assertStatus(400);
    }

    public function testPreOrderCreationWithNoCart()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $phone = '+1' . $this->faker->numberBetween(201, 999) . $this->faker->numberBetween(1000000, 9999999);

        $response = $this->actingAs($user)->postJson('/api/v1/pre-order', [
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $phone,
            'status' => 'waiting'
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Cart not found']);
    }

    public function testUpdate()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $phone = '+1' . $this->faker->numberBetween(201, 999) . $this->faker->numberBetween(1000000, 9999999);
        $preOrder = PreOrder::factory()->create([
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $phone,
            'status' => 'waiting'
        ]);

        $response = $this->withoutMiddleware()->actingAs($user)->putJson('/api/v1/pre-order/' . $preOrder->id, [
            'status' => 'approved'
        ]);

        $response->assertStatus(200);
    }

    public function testWrongStatusValidationWithUpdate()
    {
        $user = User::factory()->create([
            'email' => $this->faker->safeEmail,
            'password' => bcrypt('password'),
        ]);
        $phone = '+1' . $this->faker->numberBetween(201, 999) . $this->faker->numberBetween(1000000, 9999999);
        $preOrder = PreOrder::factory()->create([
            'user_id' => $user->id,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'phone' => $phone,
            'status' => 'waiting'
        ]);

        $response = $this->withoutMiddleware()->actingAs($user)->putJson('/api/v1/pre-order/' . $preOrder->id, [
            'status' => 'wrong'
        ]);

        $response->assertStatus(400);
    }
}
