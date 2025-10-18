<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->constrained('imports')->cascadeOnDelete();
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->constrained('imports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });
    }
};
