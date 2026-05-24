<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Convert reports.narrative to utf8mb4 so 4-byte UTF-8 (emoji,
        // rare Unicode) can be stored. Other columns keep current encoding.
        DB::statement("ALTER TABLE reports MODIFY narrative LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reports MODIFY narrative LONGTEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci");
    }
};
