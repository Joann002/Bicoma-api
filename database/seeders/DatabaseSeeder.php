<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a demo account.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Joann',
            'email' => 'demo@bicoma.test',
            'password' => Hash::make('password'),
        ]);

        $tags = collect(['favori', 'sci-fi', 'à relire', 'coup de cœur'])
            ->mapWithKeys(fn ($name) => [$name => $user->tags()->create(['name' => $name])->id]);

        $samples = [
            ['type' => 'book', 'title' => 'Dune', 'creator' => 'Frank Herbert', 'status' => 'done', 'rating' => 5, 'genre' => 'Science-fiction'],
            ['type' => 'book', 'title' => 'Fondation', 'creator' => 'Isaac Asimov', 'status' => 'in_progress', 'rating' => null, 'genre' => 'Science-fiction'],
            ['type' => 'movie', 'title' => 'Blade Runner 2049', 'creator' => 'Denis Villeneuve', 'status' => 'done', 'rating' => 5, 'genre' => 'Science-fiction'],
            ['type' => 'series', 'title' => 'Severance', 'creator' => 'Dan Erickson', 'status' => 'in_progress', 'rating' => null, 'genre' => 'Thriller'],
            ['type' => 'game', 'title' => 'Hades', 'creator' => 'Supergiant Games', 'status' => 'done', 'rating' => 4, 'genre' => 'Rogue-like'],
            ['type' => 'game', 'title' => 'Hollow Knight: Silksong', 'creator' => 'Team Cherry', 'status' => 'wishlist', 'rating' => null, 'genre' => 'Metroidvania'],
            ['type' => 'book', 'title' => 'Le Problème à trois corps', 'creator' => 'Liu Cixin', 'status' => 'wishlist', 'rating' => null, 'genre' => 'Science-fiction'],
        ];

        foreach ($samples as $sample) {
            $item = $user->items()->create([
                ...$sample,
                'added_at' => now()->subDays(random_int(0, 60)),
                'finished_at' => $sample['status'] === 'done' ? now()->subDays(random_int(0, 30)) : null,
            ]);

            if ($sample['rating'] === 5) {
                $item->tags()->attach([$tags['favori'], $tags['coup de cœur']]);
            }
        }

        $user->readingChallenges()->create([
            'year' => now()->year,
            'target_count' => 30,
        ]);

        // Un prêt en cours.
        $lent = $user->items()->where('title', 'Dune')->first();
        $lent?->loans()->create([
            'borrower_name' => 'Camille',
            'loan_date' => now()->subDays(10),
            'return_date' => now()->addDays(4),
            'returned' => false,
        ]);
    }
}
