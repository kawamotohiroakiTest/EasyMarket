<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\AuthController;

use App\Models\{User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/*
 * 会員本登録API
 */
class SignupVerifyTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 正常系
     */
    public function test_signup_verify()
    {
        //仮登録ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
        $expires = Carbon::now()->addMinutes(60);
        $signature = hash_hmac(
            'sha256',
            $user->getKey() . $expires->getTimestamp(),
            config('app.key')
        );

        $response = $this->postJson('/easymarket/api/auth/signup/verify', [
            'id' => $user->getKey(),
            'expires' => $expires->getTimestamp(),
            'signature' => $signature,
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

        // ユーザーが認証されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);
    }
    
    /*
     * トークンが不正なケース
     */
    public function test_signup_verify_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
        $expires = Carbon::now()->addMinutes(60);
        $signature = hash_hmac(
            'sha256',
            $user->getKey() . $expires->getTimestamp(),
            config('app.key')
        );

        //不正なsignatureを送信
        $response = $this->postJson('/easymarket/api/auth/signup/verify', [
            'id' => $user->getKey(),
            'expires' => $expires->getTimestamp(),
            'signature' => 'invalid-' . $signature,
        ]);

        $response->assertStatus(400)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('message')
        );

        // ユーザーが認証されていないままであることを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
    }

    /*
     * バリデーションエラー
     */
    public function test_signup_verify_validation_error()
    {
        $response = $this->postJson('/easymarket/api/auth/signup/verify', []);

        $response->assertStatus(422)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->has('message')
                ->has('errors', 3)
                ->has(
                    'errors.0',
                    fn ($json) => $json->where('field', 'id')
                        ->has('detail')
                )
                ->has(
                    'errors.1',
                    fn ($json) => $json->where('field', 'expires')
                        ->has('detail')
                )
                ->has(
                    'errors.2',
                    fn ($json) => $json->where('field', 'signature')
                        ->has('detail')
                )
        );
    }
}