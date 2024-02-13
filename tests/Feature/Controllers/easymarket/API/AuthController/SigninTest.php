<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\AuthController;

use App\Models\{User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
 * ログインAPI
 */
class SigninTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 正常系
     */
    public function test_signin()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('test-password'),
        ]);

        $response = $this->postJson('/easymarket/api/auth/signin', [
            'email' => 'test@example.com',
            'password' => 'test-password',
        ]);

        $response->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->whereType('access_token', 'string')
                ->where('user.id', $user->id)
                ->where('user.email', $user->email)
                ->where('user.name', $user->name)
                ->where('user.postal_code', $user->postal_code)
                ->where('user.address', $user->address)
                ->where('user.tel', $user->tel)
                ->where('user.nickname', $user->nickname)
                ->where('user.profile_image_url', $user->present()->profileImageUrl)
                ->where('user.description', $user->description)
        );
        $accessToken = $response['access_token'];

        $response = $this->getJson('/easymarket/api/me', [
            'Authorization' => 'Bearer ' . $accessToken,
        ]);
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
     * メールアドレス認証が完了していないケース
     */
    public function test_signin_unverified()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('test-password'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/easymarket/api/auth/signin', [
            'email' => 'test@example.com',
            'password' => 'test-password',
        ]);
        $response->assertStatus(401);
    }

    /*
     * ログイン情報が誤っているケース
     */
    public function test_signin_invalid_info()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('test-password'),
        ]);

        // メールアドレスが誤っている
        $response = $this->postJson('/easymarket/api/auth/signin', [
            'email' => 'test-invalid@example.com',
            'password' => 'test-password',
        ]);
        $response->assertStatus(400);

        // パスワードが誤っている
        $response = $this->postJson('/easymarket/api/auth/signin', [
            'email' => 'test@example.com',
            'password' => 'invalid-password',
        ]);
        $response->assertStatus(400);
    }

    /*
     * バリデーションエラー
     */
    public function test_signin_validation_error()
    {
        $response = $this->postJson('/easymarket/api/auth/signin', []);

        $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors', 2)
                ->has(
                    'errors.0',
                    fn ($json) => $json->where('field', 'email')
                        ->has('detail')
                )
                ->has(
                    'errors.1',
                    fn ($json) => $json->where('field', 'password')
                        ->has('detail')
                )
        );
    }
}