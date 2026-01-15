<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // keep as VARCHAR(20) if you already set it (works best for flexibility)
            $table->string('status', 20)->default('active')->index()->change();

            $table->dateTime('suspended_until')->nullable()->after('status');
            $table->dateTime('banned_at')->nullable()->after('suspended_until');
            $table->text('restricted_reason')->nullable()->after('banned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['suspended_until', 'banned_at', 'restricted_reason']);
        });
    }
};
