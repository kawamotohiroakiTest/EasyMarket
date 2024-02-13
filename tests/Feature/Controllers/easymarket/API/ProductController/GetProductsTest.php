<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Models\{Deal, DealEvent, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_products(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $products = Product::factory()->count(3)->create();

        $deals = Deal::factory()->count(3)->state(new Sequence(
            ['seller_id' => $seller->id, 'buyer_id' => $buyer->id, 'product_id' => $products[0]->id, 'status' => 'purchased'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[1]->id, 'status' => 'listing'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[2]->id, 'status' => 'listing']
        ))->create();

        $deals->each(function($deal) use ($seller) {
            DealEvent::factory()->for($deal)->for($seller, 'deal_eventable')->create(['actor_type' => 'seller', 'event_type' => 'listing']);
        });

        DealEvent::factory()->for($deals[0])->for($buyer, 'deal_eventable')->create(['actor_type' => 'buyer', 'event_type' => 'purchase']);

        $response = $this->getJson('/easymarket/api/products');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 3))
            ->assertJsonStructure([
                'products' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'image_url',
                        'image_urls',
                        'price',
                        'deal' => [
                            'id',
                            'is_purchasable',
                            'seller_info' => [
                                'id',
                                'nickname',
                                'profile_image_url',
                                'description',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * データが0件の時
     */
    public function test_get_products_length_zero(): void
    {
        $this->assertDatabaseCount('products', 0);

        $response = $this->getJson('/easymarket/api/products');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('products', 0));
    }
}
