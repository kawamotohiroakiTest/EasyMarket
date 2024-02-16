<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Enums\{DealEventActorType, DealEventEventType, DealStatus};
use App\Models\{Deal, DealEvent, Image, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportReceiptDealTest extends TestCase
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
    public function test_report_receipt_deal(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->buyer, 'buyer')->for($this->product, 'product')->create([
            'status' => DealStatus::Shipping,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(3)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::Purchase],
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::ReportDelivery],
        ))->create();

        // $deal.statusがshippingであることを確認
        $this->assertEquals(DealStatus::Shipping, $deal->status);
        // deal_eventsの件数が3件であることを確認
        $this->assertEquals(3, $deal->dealEvents()->count());

        $response = $this->actingAs($this->buyer)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_receipt');
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

        // $deal.statusがcompletedであることを確認
        $this->assertEquals(DealStatus::Completed, $deal->refresh()->status);
        // deal_eventsの件数が4件であることを確認
        $this->assertEquals(4, $deal->dealEvents()->count());
        
    }

    /**
     * ステータスがshipping以外の場合はエラー
     */
    public function test_report_receipt_deal_with_invalid_status(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->buyer, 'buyer')->for($this->product, 'product')->create([
            'status' => DealStatus::Completed,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(4)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::Purchase],
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::ReportDelivery],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::ReportReceipt],
        ))->create();

        // $deal.statusがcompletedであることを確認
        $this->assertEquals(DealStatus::Completed, $deal->status);
        // deal_eventsの件数が4件であることを確認
        $this->assertEquals(4, $deal->dealEvents()->count());

        $response = $this->actingAs($this->buyer)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_receipt');
        $response->assertStatus(400);

        // $deal.statusがcompletedであることを確認
        $this->assertEquals(DealStatus::Completed, $deal->refresh()->status);
        // deal_eventsの件数が4件のままであることを確認
        $this->assertEquals(4, $deal->dealEvents()->count());
        
    }


    /**
     * 購入者でない人が受取報告しようとした場合は認可エラー
     */
    public function test_report_receipt_deal_with_invalid_buyer(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->buyer, 'buyer')->for($this->product, 'product')->create([
            'status' => DealStatus::Shipping,
        ]);
        DealEvent::factory()->for($deal)->for($this->seller, 'deal_eventable')->count(3)->state(new Sequence(
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::Listing],
            ['actor_type' => DealEventActorType::Buyer, 'event_type' => DealEventEventType::Purchase],
            ['actor_type' => DealEventActorType::Seller, 'event_type' => DealEventEventType::ReportDelivery],
        ))->create();

        // $deal.statusがshippingであることを確認
        $this->assertEquals(DealStatus::Shipping, $deal->status);
        // deal_eventsの件数が3件であることを確認
        $this->assertEquals(3, $deal->dealEvents()->count());

        $response = $this->actingAs($this->other)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/report_receipt');
        $response->assertStatus(403);

        // $deal.statusがshippingであることを確認
        $this->assertEquals(DealStatus::Shipping, $deal->status);
        // deal_eventsの件数が3件のままであることを確認
        $this->assertEquals(3, $deal->dealEvents()->count());
        
    }

}
