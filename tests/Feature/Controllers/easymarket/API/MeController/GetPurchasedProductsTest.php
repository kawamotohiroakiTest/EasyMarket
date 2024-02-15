<?php

namespace Tests\Feature\Controllers\easymarket\API\MePurchasredProductController;

use App\Models\{Deal, DealEvent, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetPurchasedProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_purchased_products(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $other = User::factory()->create();

        $products = Product::factory()->count(3)->create();

        //buyerは2つの商品を購入、sellerとotherは商品を購入しないデータを作成
        $deals = Deal::factory()->count(3)->state(new Sequence(
            ['seller_id' => $seller->id, 'buyer_id' => $buyer->id, 'product_id' => $products[0]->id, 'status' => 'purchased'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[1]->id, 'status' => 'listing'],
            ['seller_id' => $other->id, 'buyer_id' => $buyer->id, 'product_id' => $products[2]->id, 'status' => 'listing']
        ))->create();

        //buyerがログインしていると購入した商品が2つある
        $response = $this->actingAs($buyer)->getJson('/easymarket/api/me/purchased_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 2));

        //sellerがログインしていると購入した商品がない
        $response = $this->actingAs($seller)->getJson('/easymarket/api/me/purchased_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 0));

        //otherがログインしていると購入した商品がない
        $response = $this->actingAs($other)->getJson('/easymarket/api/me/purchased_products');
        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 0));
    }
}
