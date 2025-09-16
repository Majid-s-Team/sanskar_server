<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');             // who created it
            $table->unsignedBigInteger('gurukal_id')->nullable(); // class id (copied from teacher)
            $table->date('date');                                // date of the update (weekly date)
            $table->string('title')->nullable();                 // optional title
            $table->text('description')->nullable();
            $table->json('media')->nullable();                   // json array of URLs [{type:'image', url:'...'}, ...] or simple url strings
            $table->timestamps();
            $table->softDeletes();

            // indexes / foreign keys (optional constraints)
            $table->index('teacher_id');
            $table->index('gurukal_id');

            // If you use foreign keys:
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('gurukal_id')->references('id')->on('gurukals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_updates');
    }
};
