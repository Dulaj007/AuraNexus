<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);          // display name
            $table->string('slug', 140)->unique(); // for URLs later
            $table->json('aliases')->nullable();   // optional: other spellings
            $table->unsignedInteger('posts_count')->default(0); // optional cache
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
