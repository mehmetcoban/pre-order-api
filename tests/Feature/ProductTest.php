<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testIndex()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
    }

    public function testShow()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->id);

        $response->assertStatus(200);
    }
}
