<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\AuthController;

use App\Models\{User};
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SignoutTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 正常系
     */
    public function test_signout()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('test-password'),
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $loginResponse = $this->postJson('/easymarket/api/auth/signin', [
            'email' => 'test@example.com',
            'password' => 'test-password',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $accessToken = $loginResponse['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/easymarket/api/auth/signout');

        /*
         * テスト実行環境の都合でログイン状態が保持されてしまうため
         * 代わりにpersonal_access_tokens テーブルからアクセストークンが削除されていることをテストする
         */
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
