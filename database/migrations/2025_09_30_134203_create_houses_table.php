<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('houses', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    // ✅ add house_id in students table
    Schema::table('students', function (Blueprint $table) {
        $table->foreignId('house_id')->nullable()->after('school_grade_id')->constrained('houses')->nullOnDelete();
    });
}

public function down()
{
    Schema::dropIfExists('houses');

    Schema::table('students', function (Blueprint $table) {
        $table->dropConstrainedForeignId('house_id');
    });
}

};
