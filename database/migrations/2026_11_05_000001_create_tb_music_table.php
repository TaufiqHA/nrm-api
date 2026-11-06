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
        Schema::create('tb_music', function (Blueprint $table) {
            $table->id('musicid');
            $table->string('musictitle');
            $table->string('musicartist')->nullable();
            $table->string('musicfileid');
            $table->string('musicfilename')->nullable();
            $table->string('musicmime', 100)->nullable();
            $table->unsignedInteger('musicduration')->nullable();
            $table->string('musiccover')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_music');
    }
};
