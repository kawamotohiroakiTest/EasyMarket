<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\ProductController;

use App\Models\{User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_post_product(): void
    {
        $user = User::factory()->create();

        Storage::fake('public');

        $data = [
            'name' => 'ABCバッグ',
            'description' => 'ABCバッグです。新品です。',
            'price' => 1234,
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->image('image3.jpg')
            ],
        ];
        $this->assertDatabaseCount('products', 0);

        $response = $this->actingAs($user)
                         ->postJson('/easymarket/api/products', $data);

        $response->assertStatus(201)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->whereType('id', 'integer')
                ->where('name', $data['name'])
                ->where('description', $data['description'])
                ->whereType('image_url', 'string') 
                ->whereType('image_urls', 'array')
                ->has('image_urls', 3)
                ->where('price', $data['price'])
                ->whereType('deal.id', 'integer')
                ->where('deal.is_purchasable', true)
                ->where('deal.seller_info.id', $user->id)
                ->where('deal.seller_info.nickname', $user->nickname)
                ->where('deal.seller_info.profile_image_url', $user->present()->profileImageUrl)
                ->where('deal.seller_info.description', $user->description)
        );
        $this->assertDatabaseCount('products', 1);
    }

    /**
     * ニックネームが空白の場合は出品できない
     */
    public function test_post_product_nickname_is_empty(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'nickname' => ''
        ]);

        $data = [
            'name' => 'ABCバッグ',
            'description' => 'ABCバッグです。新品です。',
            'price' => 1234,
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->image('image3.jpg')
            ],
        ];

        $this->assertDatabaseCount('products', 0);

        $response = $this->actingAs($user)
                         ->postJson('/easymarket/api/products', $data);

        $response->assertStatus(400);
        $this->assertDatabaseCount('products', 0);
        
    }

    /**
     * バリデーションエラーの時
     */
    public function test_post_product_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/easymarket/api/products', []);

        $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors', 4)
                ->has(
                    'errors.0',
                    fn ($json) => $json->where('field', 'name')
                        ->has('detail')
                )
                ->has(
                    'errors.1',
                    fn ($json) => $json->where('field', 'description')
                        ->has('detail')
                )
                ->has(
                    'errors.2',
                    fn ($json) => $json->where('field', 'price')
                        ->has('detail')
                )
                ->has(
                    'errors.3',
                    fn ($json) => $json->where('field', 'images')
                        ->has('detail')
                )
        );
    }
}
