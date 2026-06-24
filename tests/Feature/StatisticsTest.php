<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_report_challenge_progress(): void
    {
        $user = User::factory()->create();
        $user->readingChallenges()->create(['year' => now()->year, 'target_count' => 10]);

        Item::factory()->for($user)->count(3)->create([
            'type' => 'book',
            'status' => 'done',
            'finished_at' => now(),
        ]);

        $this->actingAs($user)->getJson('/api/stats')
            ->assertOk()
            ->assertJsonPath('data.challenge.target', 10)
            ->assertJsonPath('data.challenge.completed', 3)
            ->assertJsonPath('data.challenge.percentage', 30);
    }
}
