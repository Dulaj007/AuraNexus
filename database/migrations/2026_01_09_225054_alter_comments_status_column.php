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
    DB::statement("ALTER TABLE comments MODIFY status ENUM('published','pending') NOT NULL DEFAULT 'pending'");
}

public function down(): void
{
    // optional rollback (keep simple)
    DB::statement("ALTER TABLE comments MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
}

};
