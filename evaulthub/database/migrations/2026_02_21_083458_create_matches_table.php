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
        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 500);
            $table->string('league', 255)->nullable();
            $table->string('team_home', 255)->nullable();
            $table->string('team_away', 255)->nullable();
            $table->dateTime('match_datetime')->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedInteger('server_id')->nullable();
            $table->string('slug', 500)->unique();
            $table->string('fingerprint', 64)->unique();
            $table->enum('status', ['upcoming', 'live', 'finished'])->default('upcoming');
            $table->boolean('is_featured')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers');
            $table->index('server_id');
            $table->index('match_datetime');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
