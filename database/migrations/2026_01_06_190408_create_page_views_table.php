<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('viewable_type'); // Post, Forum, User, Tag, Search, Page
            $table->unsignedBigInteger('viewable_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_guest')->default(true);
            $table->ipAddress('ip_address');
            $table->string('user_agent');
            $table->string('referrer')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamps();

            $table->index(['viewable_type','viewable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
