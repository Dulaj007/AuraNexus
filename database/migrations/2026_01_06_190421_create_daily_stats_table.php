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
        Schema::create('daily_stats', function(Blueprint $table){
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('total_views')->default(0);
            $table->unsignedBigInteger('guest_views')->default(0);
            $table->unsignedBigInteger('registered_views')->default(0);
            $table->unsignedBigInteger('posts_created')->default(0);
            $table->unsignedBigInteger('comments_created')->default(0);
            $table->unsignedBigInteger('new_users')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};
