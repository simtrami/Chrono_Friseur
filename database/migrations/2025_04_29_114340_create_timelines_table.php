<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('picture')->nullable();
            $table->foreignIdFor(User::class)->constrained('users');
            $table->timestamps();
            $table->index(['slug', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
