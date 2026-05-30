<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tdnet_leads', function (Blueprint $table) {
            $table->enum('email_quality', ['ok', 'stale', 'invalid', 'unknown'])
                ->default('unknown')->after('email');
            $table->string('email_quality_reason')->nullable()->after('email_quality');
            $table->timestamp('refreshed_at')->nullable()->after('source_meta');
        });
    }

    public function down(): void
    {
        Schema::table('tdnet_leads', function (Blueprint $table) {
            $table->dropColumn(['email_quality', 'email_quality_reason', 'refreshed_at']);
        });
    }
};
