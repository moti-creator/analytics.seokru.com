<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boost_submissions', function (Blueprint $t) {
            $t->json('gist_result')->nullable()->after('websub_result');
            $t->json('bluesky_result')->nullable()->after('gist_result');
            $t->json('telegram_result')->nullable()->after('bluesky_result');
        });
    }

    public function down(): void
    {
        Schema::table('boost_submissions', function (Blueprint $t) {
            $t->dropColumn(['gist_result', 'bluesky_result', 'telegram_result']);
        });
    }
};
