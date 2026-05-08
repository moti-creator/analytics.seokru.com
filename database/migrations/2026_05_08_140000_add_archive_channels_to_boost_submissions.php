<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boost_submissions', function (Blueprint $t) {
            $t->json('wayback_result')->nullable()->after('reddit_result');
            $t->json('archive_today_result')->nullable()->after('wayback_result');
            $t->json('websub_result')->nullable()->after('archive_today_result');
        });
    }

    public function down(): void
    {
        Schema::table('boost_submissions', function (Blueprint $t) {
            $t->dropColumn(['wayback_result', 'archive_today_result', 'websub_result']);
        });
    }
};
