<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_users', function (Blueprint $table) {
            $table->string('verification_token')->unique()->change();
            $table->timestamp('expires_at')->nullable()->after('verification_token');
        });
    }

    public function down(): void
    {
        Schema::table('pending_users', function (Blueprint $table) {
            $table->dropColumn('expires_at');
            // if needed: remove unique
            $table->string('verification_token')->change();
        });
    }
};
