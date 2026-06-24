<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['book', 'movie', 'series', 'game']);
            $table->string('title');
            $table->string('creator')->nullable();
            $table->string('cover_url')->nullable();
            $table->enum('status', ['wishlist', 'in_progress', 'done', 'abandoned'])
                ->default('wishlist');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('genre')->nullable();
            $table->string('external_id')->nullable();
            $table->string('external_source')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
