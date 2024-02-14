<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Models\{Deal, DealEvent, Image, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_product(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $products = Product::factory()->count(3)->create();
        $images = Image::factory()->count(3)->create();
        $products->each(function ($product, $index) use ($images) {
            $product->images()->attach($images[$index]);
        });

        $deals = Deal::factory()->count(3)->state(new Sequence(
            ['seller_id' => $seller->id, 'buyer_id' => $buyer->id, 'product_id' => $products[0]->id, 'status' => 'purchased'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[1]->id, 'status' => 'listing'],
            ['seller_id' => $seller->id, 'buyer_id' => null, 'product_id' => $products[2]->id, 'status' => 'listing']
        ))->create();

        $deals->each(function($deal) use ($seller) {
            DealEvent::factory()->for($deal)->for($seller, 'deal_eventable')->create(['actor_type' => 'seller', 'event_type' => 'listing']);
        });
        DealEvent::factory()->for($deals[0])->for($buyer, 'deal_eventable')->create(['actor_type' => 'buyer', 'event_type' => 'purchase']);

        $response = $this->getJson('/easymarket/api/products/' . $deals->get(0)->product_id);

        $product = $products->get(0);
        $deal = $products->get(0)->deal;

        $response->assertStatus(200)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->where('id', $product->id)
                    ->where('name', $product->name)
                    ->where('description', $product->description)
                    ->whereType('image_url', 'string') 
                    ->whereType('image_urls', 'array')
                    ->where('price', $product->price)
                    ->where('deal.id', $deal->id)
                    ->where('deal.is_purchasable', $deal->is_purchasable)
                    ->where('deal.seller_info.id', $deal->seller->id)
                    ->where('deal.seller_info.nickname', $deal->seller->nickname)
                    ->where('deal.seller_info.profile_image_url', $deal->seller->present()->profileImageUrl)
                    ->where('deal.seller_info.description', $deal->seller->description)
        );
    }

    /**
     * 存在しないIDの時
     */
    public function test_get_product_not_found(): void
    {
        $response = $this->getJson('/easymarket/api/products/0');

        $response->assertStatus(404);
    }
}
