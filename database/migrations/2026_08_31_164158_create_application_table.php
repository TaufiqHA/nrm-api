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
        Schema::create('tb_application', function (Blueprint $table) {
            $table->increments('applicationid');
            $table->string('applicationcompany', 100);
            $table->string('applicationname', 255);
            $table->text('applicationads1')->nullable();
            $table->text('applicationads2')->nullable();
            $table->string('applicationadsactive', 1)->default('Y');
            $table->text('applicationadsbottom')->nullable();
            $table->string('applicationadsbottomactive', 1)->default('Y');
            $table->timestamps();

            $table->comment('Konfigurasi aplikasi dan iklan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_application');
    }
};
