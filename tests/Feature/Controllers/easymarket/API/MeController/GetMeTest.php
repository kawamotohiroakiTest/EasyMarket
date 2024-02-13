<?php

namespace Tests\Feature\Feature\Controllers\easymarket\API\MeController;

use App\Models\{User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetMeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常系
     */
    public function test_get_me(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/easymarket/api/me');

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
}
