<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('primary_email')->unique();
            $table->string('secondary_email')->nullable();
            $table->string('mobile_number');
            $table->string('secondary_mobile_number')->nullable();
            $table->string('father_name');
            $table->string('mother_name');
            $table->boolean('father_volunteering')->default(false);
            $table->boolean('mother_volunteering')->default(false);
            $table->boolean('is_hsnc_member')->default(false);

            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip_code', 20)->nullable();

            $table->boolean('is_active')->default(false);
            $table->boolean('is_payment_done')->default(false);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
