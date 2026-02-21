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
        Schema::create('match_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('match_id');
            $table->unsignedInteger('server_id')->nullable();
            $table->string('match_title', 500)->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->index('viewed_at', 'idx_viewed_at');
            $table->index('match_id', 'idx_match_id');
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_views');
    }
};
