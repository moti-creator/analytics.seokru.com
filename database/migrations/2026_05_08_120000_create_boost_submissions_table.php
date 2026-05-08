<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boost_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('connection_id')->nullable()->constrained()->nullOnDelete();
            $t->string('url', 1024);
            $t->string('domain', 255)->index();
            // Per-channel results (json)
            $t->json('indexnow_result')->nullable();
            $t->json('indexing_api_result')->nullable();
            $t->json('llms_txt_result')->nullable();
            $t->json('reddit_result')->nullable();
            // Follow-up tracking
            $t->json('inspection_24h')->nullable();
            $t->json('inspection_72h')->nullable();
            $t->json('inspection_7d')->nullable();
            $t->boolean('indexed')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['connection_id', 'created_at']);
            $t->index(['url', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_submissions');
    }
};
