<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedTinyInteger('participation_points')->default(0)->after('status');
            $table->unsignedTinyInteger('homework_points')->default(0)->after('participation_points');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['participation_points', 'homework_points']);
        });
    }
};
