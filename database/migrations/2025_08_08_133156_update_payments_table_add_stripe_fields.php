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
    Schema::table('payments', function (Blueprint $table) {
        $table->string('payment_method')->nullable();
        $table->string('card_brand')->nullable();
        $table->string('card_last4', 4)->nullable();
        $table->string('card_exp_month')->nullable();
        $table->string('card_exp_year')->nullable();
        $table->string('billing_name')->nullable();
        $table->string('billing_email')->nullable();
        $table->string('billing_country')->nullable();
        $table->string('billing_city')->nullable();
        $table->string('billing_line1')->nullable();
        $table->string('billing_postal_code')->nullable();
        $table->text('error_message')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
