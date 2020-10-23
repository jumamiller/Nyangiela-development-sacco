<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone_number')->nullable()->unique();
            $table->text('transactions')->nullable();
            $table->double('savings')->nullable();
            $table->double('loan')->nullable();
            $table->double('lock_savings')->nullable();
            $table->double('lock_savings_target')->nullable();
            $table->double('loan_limit')->nullable();
            $table->dateTime('loan_taken_on')->nullable();
            $table->dateTime('loan_due_date')->nullable();
            $table->dateTime('lock_savings_maturity_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account');
    }
}
