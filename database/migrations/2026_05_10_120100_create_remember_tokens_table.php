<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('remember_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('connection_id')->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();
            $t->timestamp('expires_at')->index();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('remember_tokens');
    }
};
