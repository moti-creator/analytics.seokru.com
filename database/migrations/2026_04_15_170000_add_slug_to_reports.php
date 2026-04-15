<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->string('slug', 32)->nullable()->unique()->after('id');
        });

        // Backfill existing reports with slugs
        DB::table('reports')->whereNull('slug')->orderBy('id')->each(function ($r) {
            DB::table('reports')->where('id', $r->id)->update([
                'slug' => Str::random(16),
            ]);
        });
    }
    public function down(): void {
        Schema::table('reports', fn(Blueprint $t) => $t->dropColumn('slug'));
    }
};
