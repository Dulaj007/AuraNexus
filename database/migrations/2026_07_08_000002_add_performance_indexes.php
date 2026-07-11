<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('status');
            $table->index(['status', 'created_at']);
        });

        Schema::table('page_views', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('is_guest');
        });

        // `path` is a VARCHAR(2048); a full-column index would exceed MySQL's
        // InnoDB key-length limit under utf8mb4, so index a prefix instead.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE page_views ADD INDEX page_views_path_index (path(191))');
        } else {
            Schema::table('page_views', function (Blueprint $table) {
                $table->index('path');
            });
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('page_views', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_guest']);
            $table->dropIndex(['path']);
        });
    }
};
