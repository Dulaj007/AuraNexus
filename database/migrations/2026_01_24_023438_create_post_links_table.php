<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_links', function (Blueprint $table) {
            $table->id();

            // relates to your existing posts table
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();

            // short code used in URL: /link/{code}
            $table->string('code', 32)->unique();

            // Store full URL (recommended: encrypt in model cast)
            $table->text('original_url');

            // optional label shown to users (e.g. "Download 1", "Watch Online")
            $table->string('label', 120)->nullable();

            // download/watch/other
            $table->string('type', 30)->default('download');

            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->index(['post_id', 'is_enabled']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_links');
    }
};
