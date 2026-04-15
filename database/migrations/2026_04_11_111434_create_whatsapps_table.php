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
        Schema::create('whatsapps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institutionId')->nullable();
            $table->string('device');
            $table->boolean('active')->default(false);
            $table->enum('status',[1, 2, 3])->default(1)->comment('1. disconnected, 2. logged');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapps');
    }
};
