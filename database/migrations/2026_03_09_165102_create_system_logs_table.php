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
        Schema::create('system_logs', function (Blueprint $row) {
            $row->id();
            $row->foreignId('userId')->nullable()->constrained('users')->onDelete('cascade');
            $row->string('level')->default('info'); // info, warning, error, transaction
            $row->text('message');
            $row->json('context')->nullable();
            $row->string('ip_address', 45)->nullable();
            $row->text('user_agent')->nullable();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
