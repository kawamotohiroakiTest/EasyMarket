<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Enums\{DealEventActorType, DealEventEventType, DealStatus};
use App\Models\{Deal, DealEvent, Image, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CancelListingProductTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $other;
    protected $product;
    protected $image;
    protected $deal;

    public function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->other = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->image = Image::factory()->create();
        $this->product->images()->attach($this->image);
    }

    /**
     * 正常系
     */
    public function test_cancel_listing_product(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->product, 'product')->create([
            'status' => DealStatus::Listing,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(1)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
        ))->create();

        // $deal.statusがlistingであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->status);
        // deal_eventsの件数が1件であることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());

        $response = $this->actingAs($this->seller)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/cancel');
        $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'is_purchasable',
            'seller_info' => [
                'id',
                'nickname',
                'profile_image_url',
                'description',
            ],
            'buyer_shipping_info' => [
                'id',
                'name',
                'postal_code',
                'address',
                'nickname',
                'profile_image_url',
                'description',
            ],
            'status',
            'deal_events' => [
                '*' => [
                    'id',
                    'actor_type',
                    'event_type',
                ],
            ],
        ]);

        // $deal.statusがcanceledであることを確認
        $this->assertEquals(DealStatus::Canceled, $deal->refresh()->status);
        // deal_eventsの件数が2件であることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());
        
    }

    /**
     * ステータスがlisting以外の場合はエラー
     */
    public function test_cancel_listing_product_with_not_listing_status(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->product, 'product')->create([
            'status' => DealStatus::Purchased,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(1)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
        ))->create();

        // $deal.statusがpurchasedのままであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->status);
        // deal_eventsの件数が1件のままであることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());

        $response = $this->actingAs($this->seller)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/cancel');
        $response->assertStatus(400);

        // $deal.statusがpurchasedであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->refresh()->status);
        // deal_eventsの件数が1件であることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());
    }


    /**
     * 出品者でない人がキャンセルしようとした場合は認可エラー
     */
    public function test_cancel_listing_product_with_not_seller(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->product, 'product')->create([
            'status' => DealStatus::Listing,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(1)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
        ))->create();

        // $deal.statusがlistingであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->status);
        // deal_eventsの件数が1件であることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());

        $response = $this->actingAs($this->other)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/cancel');
        $response->assertStatus(403);

        // $deal.statusがlistingであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->refresh()->status);
        // deal_eventsの件数が1件であることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());
    }

}
