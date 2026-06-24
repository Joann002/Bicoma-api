<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_lend_an_item_and_mark_it_returned(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/loans', [
            'item_id' => $item->id,
            'borrower_name' => 'Alice',
            'return_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertCreated()->assertJsonPath('data.borrower_name', 'Alice');
        $loanId = $response->json('data.id');

        $this->actingAs($user)->postJson("/api/loans/{$loanId}/return")
            ->assertOk()->assertJsonPath('data.returned', true);
    }

    public function test_overdue_endpoint_returns_late_loans(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create();
        $item->loans()->create([
            'borrower_name' => 'Bob',
            'loan_date' => now()->subMonth(),
            'return_date' => now()->subWeek(),
            'returned' => false,
        ]);

        $this->actingAs($user)->getJson('/api/loans/overdue')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.overdue', true);
    }
}
