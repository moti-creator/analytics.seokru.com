<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->string('share_token', 40)->nullable()->unique()->after('slug');
        });
    }
    public function down(): void {
        Schema::table('reports', fn(Blueprint $t) => $t->dropColumn('share_token'));
    }
};
