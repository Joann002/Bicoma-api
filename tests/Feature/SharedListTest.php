<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_shared_list_and_access_it_publicly(): void
    {
        $user = User::factory()->create();
        Item::factory()->for($user)->create(['type' => 'book', 'title' => 'Dune']);
        Item::factory()->for($user)->create(['type' => 'game', 'title' => 'Hades']);

        $response = $this->actingAs($user)->postJson('/api/shared-lists', [
            'title' => 'Mes livres',
            'filters' => ['type' => 'book'],
        ])->assertCreated();

        $token = $response->json('data.token');

        $this->getJson("/api/public/{$token}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Mes livres')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Dune');
    }

    public function test_inactive_or_unknown_token_returns_404(): void
    {
        $this->getJson('/api/public/unknown-token')->assertNotFound();
    }
}
