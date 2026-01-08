<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            if (!Schema::hasColumn('role_user', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            if (Schema::hasColumn('role_user', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
