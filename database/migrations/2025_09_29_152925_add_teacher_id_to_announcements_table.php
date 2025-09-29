<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('announcements', function (Blueprint $table) {
        // Allow NULL first to avoid constraint issues
        $table->unsignedBigInteger('teacher_id')->nullable()->after('gurukal_id');

        // Add foreign key
        $table->foreign('teacher_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade');
    });
}

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
