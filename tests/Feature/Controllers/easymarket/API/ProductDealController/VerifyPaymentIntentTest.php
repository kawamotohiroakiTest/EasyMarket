<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Enums\{DealEventActorType, DealEventEventType, DealStatus};
use App\Models\{Deal, DealEvent, Image, Product, User};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class VerifyPaymentIntentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
    public function test_verify_payment_intent(): void
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

        $response = $this->actingAs($this->buyer)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/payment_intent/verify', [
            'payment_intent_id' => $this->faker->text(20),
        ]);
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

        // $deal.statusがpurchasedであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->refresh()->status);
        // deal_eventsの件数が2件であることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());
    }

    /**
     * ステータスがlisting以外の場合はエラー
     */
    public function test_verify_payment_intent_with_not_listing_status(): void
    {
        $deal = Deal::factory()->for($this->seller, 'seller')->for($this->product, 'product')->create([
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

        $response = $this->actingAs($this->buyer)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/payment_intent/verify', [
            'payment_intent_id' => $this->faker->text(20),
        ]);
        $response->assertStatus(400);

        // $deal.statusがpurchasedのままであることを確認
        $this->assertEquals(DealStatus::Purchased, $deal->refresh()->status);
        // deal_eventsの件数が2件のままであることを確認
        $this->assertEquals(2, $deal->dealEvents()->count());
    }

    /**
     * 購入者の氏名、郵便番号、住所が未登録の場合は購入できない
     */
    public function test_verify_payment_intent_with_not_registered_shipping_info(): void
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

        $this->buyer->name = '';
        $this->buyer->postal_code = '';
        $this->buyer->address = '';
        $this->buyer->save();

        $response = $this->actingAs($this->buyer)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/payment_intent/verify', [
            'payment_intent_id' => $this->faker->text(20),
        ]);
        $response->assertStatus(400);

        // $deal.statusがlistingのままであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->refresh()->status);
        // deal_eventsの件数が1件のままであることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());
    }
        

    /**
     * 出品者が購入しようとした場合は認可エラー
     */
    public function test_verify_payment_intent_with_seller(): void
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

        $response = $this->actingAs($this->seller)->postJson('/easymarket/api/products/' . $deal->product_id . '/deal/payment_intent/verify', [
            'payment_intent_id' => $this->faker->text(20),
        ]);
        $response->assertStatus(403);

        // $deal.statusがlistingのままであることを確認
        $this->assertEquals(DealStatus::Listing, $deal->refresh()->status);
        // deal_eventsの件数が1件のままであることを確認
        $this->assertEquals(1, $deal->dealEvents()->count());
    }

}