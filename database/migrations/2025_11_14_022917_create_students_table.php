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
        Schema::create('student_personals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('name');
            $table->string('nik');
            $table->string('nisn')->nullable();
            $table->enum('gender', [1, 2])->default(1);
            $table->string('birthPlace');
            $table->date('birthDate');
            $table->string('phone')->nullable();
            $table->string('birthNumber');
            $table->string('sibling');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_parents', function ($table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('numberKk')->nullable();
            $table->string('headFamily')->nullable();
            $table->enum('fatherStatus', ['1', '2', '3'])->default('1');
            $table->string('fatherName');
            $table->string('fatherNik')->nullable();
            $table->string('fatherBirthPlace')->nullable();
            $table->date('fatherBirthDate')->nullable();
            $table->enum('fatherStudy', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->default('1');
            $table->enum('fatherJob', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18'])->default('1');
            $table->string('fatherPhone')->nullable();
            $table->enum('motherStatus', ['1', '2', '3'])->default('1');
            $table->string('motherName')->nullable();
            $table->string('motherNik')->nullable();
            $table->string('motherBirthPlace')->nullable();
            $table->date('motherBirthDate')->nullable();
            $table->enum('motherStudy', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->default('1');
            $table->enum('motherJob', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18'])->default('1');
            $table->string('motherPhone')->nullable();
            $table->enum('guardStatus', ['1', '2', '3'])->default('1');
            $table->string('guardName');
            $table->string('guardNik');
            $table->string('guardBirthPlace');
            $table->date('guardBirthDate');
            $table->enum('guardStudy', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->default('1');
            $table->enum('guardJob', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18'])->default('1');
            $table->string('guardPhone');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('village');
            $table->string('street');
            $table->string('rt');
            $table->string('rw');
            $table->string('postal');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('yearId');
            $table->unsignedBigInteger('institutionId');
            $table->unsignedBigInteger('periodId');
            $table->unsignedBigInteger('programId');
            $table->unsignedBigInteger('boardingId');
            $table->unsignedBigInteger('roomId')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('registration_token')->nullable()->unique();
            $table->timestamp('registration_generated_at')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_origins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('name');
            $table->string('npsn')->nullable();
            $table->string('address');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->enum('level', ['1', '2', '3', '4'])->default('4');
            $table->enum('champ', ['1', '2', '3', '4', '5', '6'])->default('1');
            $table->enum('type', ['1', '2'])->default('1');
            $table->string('name');
            $table->string('file');
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('filePhoto')->nullable();
            $table->string('fileKk');
            $table->string('fileKtp')->nullable();
            $table->string('numberAkta')->nullable();
            $table->string('fileAkta');
            $table->string('numberIjazah')->nullable();
            $table->string('fileIjazah')->nullable();
            $table->string('numberSkl')->nullable();
            $table->string('fileSkl')->nullable();
            $table->string('numberKip')->nullable();
            $table->string('fileKip')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->timestamps();
        });

        Schema::create('student_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('twins');
            $table->string('twinsName')->nullable();
            $table->unsignedBigInteger('graduate');
            $table->unsignedBigInteger('domicile');
            $table->unsignedBigInteger('student');
            $table->unsignedBigInteger('teacherSon');
            $table->unsignedBigInteger('sibling');
            $table->unsignedBigInteger('siblingInstitution')->nullable();
            $table->string('siblingName')->nullable();
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
        Schema::dropIfExists('student_verifications');
        Schema::dropIfExists('student_files');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('student_origins');
        Schema::dropIfExists('student_programs');
        Schema::dropIfExists('student_addresses');
        Schema::dropIfExists('student_parents');
        Schema::dropIfExists('student_personals');
    }
};
