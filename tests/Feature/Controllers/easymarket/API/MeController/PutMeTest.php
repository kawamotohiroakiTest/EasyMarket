<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\MeController;

use App\Models\{User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PutMeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_put_me(): void
    {
        $user = User::factory()->create();
        Storage::fake('public');

        $profileImage = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($user)->putJson('/easymarket/api/me', [
            'name' => 'updated-name',
            'postal_code' => '1234567',
            'address' => '新しい住所',
            'tel' => '0987654321',
            'nickname' => '太郎',
            'description' => '太郎です。',
            'profile_image' => $profileImage,
        ]);

        $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) =>
                $json->where('id', $user->id)
                ->where('email', $user->email)
                ->where('name', 'updated-name')
                ->where('postal_code', '1234567')
                ->where('address', '新しい住所')
                ->where('tel', '0987654321')
                ->where('nickname', '太郎')
                ->where('profile_image_url', $user->present()->profileImageUrl)
                ->where('description', '太郎です。')
        );

        $user->refresh();
        $this->assertEquals($user->name, 'updated-name');
        $this->assertEquals($user->postal_code, '1234567');
        $this->assertEquals($user->address, '新しい住所');
        $this->assertEquals($user->tel, '0987654321');
        $this->assertEquals($user->nickname, '太郎');
        $this->assertEquals($user->description, '太郎です。');

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk('public');
        $storageDisk->assertExists('images/'.$profileImage->hashName());

    }

    /*
     * リクエストボディが空の場合
     */
    public function test_put_me_empty_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/easymarket/api/me', []);

        $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('id', $user->id)
                ->where('email', $user->email)
                ->where('name', $user->name)
                ->where('postal_code', $user->postal_code)
                ->where('address', $user->address)
                ->where('tel', $user->tel)
                ->where('nickname', $user->nickname)
                ->where('profile_image_url', $user->present()->profileImageUrl)
                ->where('description', $user->description)
        );
    }

    /*
     * バリデーションエラー
     */
    public function test_put_me_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/easymarket/api/me', [
            'name' => null,
            'postal_code' => '100-8112',
            'address' =>null,
            'tel' => '03-1234-5679',
            'nickname' => null,
            'description' => null,
        ]);

        $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors', 6)
        );
    }
}
