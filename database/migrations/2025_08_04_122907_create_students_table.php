<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
     Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
    $table->string('first_name');
    $table->string('last_name');
    $table->date('dob');
    $table->string('student_email')->nullable();
    $table->string('student_mobile_number')->nullable();

    $table->boolean('join_the_club')->default(false);
    $table->string('school_name')->nullable();
    $table->text('hobbies_interest')->nullable();
    $table->boolean('is_school_year_around')->default(false);
    $table->string('last_year_class')->nullable();
    $table->text('any_allergies')->nullable();

    $table->foreignId('teeshirt_size_id')->constrained('teeshirt_sizes')->onDelete('restrict');
    $table->foreignId('gurukal_id')->constrained('gurukals')->onDelete('restrict');
    $table->foreignId('school_grade_id')->constrained('grades')->onDelete('restrict');

    // $table->text('address')->nullable();
    // $table->string('city', 100)->nullable();
    // $table->string('state', 100)->nullable();
    // $table->string('zip_code', 20)->nullable();

    $table->timestamps();
    $table->softDeletes();
});

    }

    public function down(): void {
        Schema::dropIfExists('students');
    }
};
