<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->string('path', 2048)->nullable()->after('referrer'); // e.g. /forums/announcements
            $table->string('url', 2048)->nullable()->after('path');     // full url if you want it
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropColumn(['path', 'url']);
        });
    }
};
