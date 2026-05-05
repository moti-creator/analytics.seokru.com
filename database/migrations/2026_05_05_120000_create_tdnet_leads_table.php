<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tdnet_leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('position')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('linkedin_url')->nullable();
            $table->string('segment')->nullable(); // hospital | academic | pharma | corporate
            $table->json('source_meta')->nullable();
            $table->text('email_subject')->nullable();
            $table->longText('email_body')->nullable();
            $table->string('email_style')->nullable(); // question | statement | pain
            $table->json('subject_variants')->nullable();
            $table->enum('status', ['new', 'sent', 'replied', 'skipped'])->default('new')->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tdnet_leads');
    }
};
