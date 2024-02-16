<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Enums\{DealEventActorType, DealEventEventType, DealStatus};
use App\Models\{Deal, DealEvent, Image, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDeliveryDealTest extends TestCase
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
    public function test_report_delivery_deal(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->buyer, 'buyer')->for($this->product, 'product')->create([
            'status' => DealStatus::Purchased,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(2)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::Purchase],
        ))->create();

        // $deal.statusがpurchasedであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->status);
        // deal_eventsの件数が2件であることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());

        $response = $this->actingAs($this->seller)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_delivery');
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

        // $deal.statusがshippingであることを確認
        $this->assertEquals(DealStatus::Shipping, $deal->refresh()->status);
        // deal_eventsの件数が3件であることを確認
        $this->assertEquals(3, $deal->dealEvents()->count());
        
    }

    /**
     * ステータスがpurchased以外の場合はエラー
     */
    public function test_report_delivery_deal_with_not_purchased_status(): void
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

        $response = $this->actingAs($this->seller)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_delivery');
        $response->assertStatus(400);

        // $deal.statusがlistingであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->refresh()->status);
        // deal_eventsの件数が1件であることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());
    }


    /**
     * 出品者でない人が配送報告しようとした場合は認可エラー
     */
    public function test_report_delivery_deal_with_not_seller(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->buyer, 'buyer')->for($this->product, 'product')->create([
            'status' => DealStatus::Purchased,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(2)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::Purchase],
        ))->create();

        // $deal.statusがpurchasedであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->status);
        // deal_eventsの件数が2件であることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());

        $response = $this->actingAs($this->other)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_delivery');
        $response->assertStatus(403);

        // $deal.statusがpurchasedであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->refresh()->status);
        // deal_eventsの件数が2件であることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());
    }

}
