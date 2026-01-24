<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_placements', function (Blueprint $table) {
            $table->id();

            // Key must match config/ads.php placements keys (e.g. category_top_a)
            $table->string('key', 120)->unique();

            // Human-friendly admin label + help
            $table->string('label', 160);
            $table->text('description')->nullable();

            // Ad snippet (HTML/JS)
            $table->longText('html')->nullable();

            // Quick toggle
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            // Helpful index for reads
            $table->index(['is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_placements');
    }
};
