<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pinned_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            $table->foreignId('pinned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('pinned_at')->useCurrent();

            $table->timestamps();

            // prevent same post being pinned twice in same forum
            $table->unique(['forum_id', 'post_id']);
            $table->index(['forum_id', 'pinned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinned_posts');
    }
};
