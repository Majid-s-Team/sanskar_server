<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('is_otp_verified');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('school_grade_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
    }

};
