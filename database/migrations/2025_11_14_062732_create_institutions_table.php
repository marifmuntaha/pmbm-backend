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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('tagline')->nullable();
            $table->string('npsn');
            $table->string('nsm');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('website');
            $table->string('head');
            $table->string('logo')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('headmaster_certificate_path')->nullable();
            $table->timestamp('certificate_generated_at')->nullable();
            $table->timestamp('certificate_expires_at')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('institution_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->string('capacity');
            $table->string('brochure');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('institution_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->string('name');
            $table->string('alias');
            $table->string('description')->nullable();
            $table->string('boarding');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('institution_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->string('name');
            $table->string('description');
            $table->date('start');
            $table->date('end');
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
        Schema::dropIfExists('institution_periods');
        Schema::dropIfExists('institution_programs');
        Schema::dropIfExists('institution_activities');
        Schema::dropIfExists('institutions');
    }
};
