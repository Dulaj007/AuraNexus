<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // What happened
            $table->string('event', 100); // e.g. "login", "save_post", "create_post"
            $table->nullableMorphs('subject'); // subject_type + subject_id (Post, Forum, etc.)

            // Request info
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1024)->nullable();

            // Extra details (optional)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
