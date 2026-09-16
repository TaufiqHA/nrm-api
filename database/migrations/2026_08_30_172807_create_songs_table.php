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
        Schema::create('tb_songs', function (Blueprint $table) {
            $table->increments('songid');
            $table->string('songtitle', 255);
            $table->string('songsinger', 255);
            $table->text('songurl');
            $table->unsignedInteger('songcategory');
            $table->string('songnada', 10)->nullable();
            $table->string('songduration', 5)->nullable();
            $table->timestamps();

            $table->foreign('songcategory')
                ->references('songcategoryid')
                ->on('categories')
                ->cascadeOnDelete();

            $table->comment('Master katalog lagu karaoke');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_songs');
    }
};
