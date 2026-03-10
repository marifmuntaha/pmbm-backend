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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->unsignedBigInteger('userId');
            $table->string('reference');
            $table->string('name');
            $table->integer('amount');
            $table->dateTime('dueDate');
            $table->enum('status', ['PAID', 'PENDING', 'UNPAID', 'FAILED', 'EXPIRED', 'REFUND']);
            $table->string('link')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('invoiceDetails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceId');
            $table->unsignedBigInteger('productId');
            $table->string('name');
            $table->integer('price');
            $table->integer('discount');
            $table->integer('amount');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoiceDetails');
        Schema::dropIfExists('invoices');
    }
};
