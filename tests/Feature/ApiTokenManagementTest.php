<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_tokens_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/tokens')->assertOk();
    }

    public function test_authenticated_user_can_create_a_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tokens', ['name' => 'my-laptop']);

        $response->assertRedirect(route('tokens.index'));
        $response->assertSessionHas('plain_text_token');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'my-laptop',
        ]);
    }

    public function test_user_can_revoke_their_own_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('to-revoke');

        $this->actingAs($user)->delete("/tokens/{$token->accessToken->id}");

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $token = $owner->createToken('not-yours');

        $this->actingAs($intruder)->delete("/tokens/{$token->accessToken->id}");

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}
