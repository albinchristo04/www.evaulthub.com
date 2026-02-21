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
        Schema::create('match_streams', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('match_id');
            $table->string('channel_name', 255)->nullable();
            $table->text('iframe_url');
            $table->enum('stream_type', ['iframe', 'm3u8'])->default('iframe');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_streams');
    }
};
