<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_item_with_tags(): void
    {
        $user = $this->actingAs(User::factory()->create());

        $response = $user->postJson('/api/items', [
            'type' => 'book',
            'title' => 'Dune',
            'creator' => 'Frank Herbert',
            'status' => 'done',
            'rating' => 5,
            'tags' => ['sci-fi', 'classique'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Dune')
            ->assertJsonPath('data.tags', ['sci-fi', 'classique']);
        $this->assertDatabaseHas('items', ['title' => 'Dune']);
        $this->assertDatabaseCount('tags', 2);
    }

    public function test_index_filters_by_type_and_status_and_search(): void
    {
        $user = User::factory()->create();
        Item::factory()->for($user)->create(['type' => 'book', 'status' => 'done', 'title' => 'Dune']);
        Item::factory()->for($user)->create(['type' => 'game', 'status' => 'wishlist', 'title' => 'Hades']);

        $this->actingAs($user)->getJson('/api/items?type=book')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Dune');

        $this->actingAs($user)->getJson('/api/items?q=Had')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Hades');
    }

    public function test_user_cannot_access_another_users_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = Item::factory()->for($owner)->create();

        $this->actingAs($other)->getJson("/api/items/{$item->id}")->assertForbidden();
        $this->actingAs($other)->deleteJson("/api/items/{$item->id}")->assertForbidden();
    }

    public function test_user_can_update_and_delete_their_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create(['status' => 'wishlist']);

        $this->actingAs($user)->putJson("/api/items/{$item->id}", ['status' => 'in_progress'])
            ->assertOk()->assertJsonPath('data.status', 'in_progress');

        $this->actingAs($user)->deleteJson("/api/items/{$item->id}")->assertNoContent();
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
