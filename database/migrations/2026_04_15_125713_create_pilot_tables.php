<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('connections', function (Blueprint $t) {
            $t->id();
            $t->string('email');
            $t->string('google_user_id')->nullable();
            $t->text('access_token');
            $t->text('refresh_token')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->string('ga4_property_id')->nullable();
            $t->string('gsc_site_url')->nullable();
            $t->timestamps();
        });

        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('connection_id')->constrained()->cascadeOnDelete();
            $t->json('metrics')->nullable();
            $t->longText('narrative')->nullable();
            $t->string('pdf_path')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('connections');
    }
};
