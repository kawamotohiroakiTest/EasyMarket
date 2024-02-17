<?php

namespace Tests\Feature\Controllers\easymarket\API\MePurchasedProductController;

use App\Enums\{DealStatus, DealEventActorType, DealEventEventType};
use App\Models\{Deal, DealEvent, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetPurchasedProductDealTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_purchased_product_deal(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $other = User::factory()->create();

        $product = Product::factory()->create();

        $deal = Deal::factory()->create([
            'seller_id' => $seller->id, 'buyer_id' => $buyer->id, 'product_id' => $product->id, 'status' => DealStatus::Purchased
        ]);
        $dealEvents = DealEvent::factory()->for($deal)->for($seller, 'deal_eventable')->count(1)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
        ))->create();

        $response = $this->actingAs($buyer)->getJson('/easymarket/api/me/purchased_products/' . $product->id . '/deal');

        $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('id', $deal->id)
                ->where('is_purchasable', false)
                ->where('seller_info.id', $deal->seller->id)
                ->where('seller_info.nickname', $deal->seller->nickname)
                ->where('seller_info.profile_image_url', $deal->seller->present()->profileImageUrl)
                ->where('seller_info.description', $deal->seller->description)
                ->where('buyer_shipping_info.id', $deal->buyer->id)
                ->where('buyer_shipping_info.name', $deal->buyer->name)
                ->where('buyer_shipping_info.postal_code', $deal->buyer->postal_code)
                ->where('buyer_shipping_info.address', $deal->buyer->address)
                ->where('buyer_shipping_info.nickname', $deal->buyer->nickname)
                ->where('buyer_shipping_info.profile_image_url', $deal->buyer->present()->profileImageUrl)
                ->where('buyer_shipping_info.description', $deal->buyer->description)
                ->where('status', $deal->status->value)
                ->where('deal_events.0.id', $dealEvents[0]->id)
                ->where('deal_events.0.actor_type', $dealEvents[0]->actor_type->value)
                ->where('deal_events.0.event_type', $dealEvents[0]->event_type->value)
        );

    }

    /**
     * 存在しないIDの時
     */
    public function test_get_purchased_product_deal_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/easymarket/api/me/purchased_products/0/deal');

        $response->assertStatus(404);
    }
}

