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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->unsignedBigInteger('userId');
            $table->string('invoiceId');
            $table->enum('method', [1, 2]);
            $table->enum('status', [1, 2]);
            $table->string('transaction_id');
            $table->string('transaction_time');
            $table->integer('amount');
            $table->string('receipt_number')->nullable()->unique();
            $table->string('receipt_token')->nullable()->unique();
            $table->timestamp('receipt_generated_at')->nullable();
            $table->unsignedBigInteger('receipt_generated_by')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy');
            $table->timestamps();
        });

         Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->boolean('is_active')->default(false);
            $table->enum('mode', [1, 2])->default(1)->comment('1 = sandbox, 2 = production');
            $table->text('server_key')->nullable();
            $table->text('client_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('callback_token')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('payments');
    }
};
