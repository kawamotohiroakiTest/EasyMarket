<?php

namespace Tests\Feature\Controllers\easymarket\API\MeListedProductController;

use App\Models\{Deal, DealEvent, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetListedProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_listed_products(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $other = User::factory()->create();

        $products = Product::factory()->count(3)->create();

        //buyerは商品を出品せず、sellerは2つの商品を出品、otherは1つの商品を出品しているデータを作成
        $deals = Deal::factory()->count(3)->state(new Sequence(
            ['seller_id' => $seller->id, 'buyer_id' => $buyer->id, 'product_id' => $products[0]->id, 'status' => 'purchased'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[1]->id, 'status' => 'listing'],
            ['seller_id' => $other->id, 'buyer_id' => $buyer->id, 'product_id' => $products[2]->id, 'status' => 'listing']
        ))->create();

        //buyerがログインしても出品している商品はない
        $response = $this->actingAs($buyer)->getJson('/easymarket/api/me/listed_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 0));

        //sellerがログインしていると出品している商品が2つある
        $response = $this->actingAs($seller)->getJson('/easymarket/api/me/listed_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 2));

        //otherがログインしていると出品している商品が1つある
        $response = $this->actingAs($other)->getJson('/easymarket/api/me/listed_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 1));
    }
}
