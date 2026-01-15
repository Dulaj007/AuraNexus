<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // If you previously had enum('active','banned'), put it here.
        // If you're not sure, keep it as VARCHAR in down too.
        DB::statement("ALTER TABLE users MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
    }
};
