<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\AuthController;

use App\Models\{User};
use App\Notifications\easymarket\SignupVerify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/*
 * 会員仮登録API
 */
class SignupTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 正常系
     */
    public function test_signup()
    {
        //通知を防ぐ
        Notification::fake();

        // ユーザーがまだ作成されていないことを確認
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);

        // APIリクエスト
        $response = $this->postJson('/easymarket/api/auth/signup', [
            'email' => 'test@example.com',
            'password' => 'test-password',
        ]);

        // 通知が送信されたことを確認
        Notification::assertSentTo(
            [User::first()],
            SignupVerify::class
        );

        // レスポンスの確認
        $response->assertStatus(201)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('success', true)
        );

        // DBから該当データが作成されていることを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);
    }

}
