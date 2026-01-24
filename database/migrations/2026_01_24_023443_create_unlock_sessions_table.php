<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unlock_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_link_id')->constrained('post_links')->cascadeOnDelete();

            // token stored client-side (sessionStorage). Must be unguessable.
            $table->string('token', 64)->unique();

            $table->unsignedTinyInteger('required_seconds')->default(5);

            // Server-side away-time accounting
            $table->unsignedInteger('away_seconds_accumulated')->default(0);
            $table->timestamp('away_started_at')->nullable();

            // heartbeat from /link/{code}
            $table->timestamp('last_ping_at')->nullable();

            $table->string('status', 20)->default('started'); // started|unlocked|expired

           $table->dateTime('expires_at')->index();


            // optional anti-abuse
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_hash', 80)->nullable()->index();
            $table->string('ua_hash', 80)->nullable()->index();

            $table->timestamps();

            $table->index(['post_link_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unlock_sessions');
    }
};
