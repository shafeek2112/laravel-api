<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('application_no',15)->unique();
            $table->string('loan_term',15);
            $table->string('repayment_frequency',10);
            $table->decimal('loan_amount', 8, 2);
            $table->decimal('each_instalment_payment_amount', 8, 2)->nullable();
            $table->decimal('total_repaid_loan_amount', 8, 2)->nullable();
            $table->string('approved_status',10);
            $table->date('application_date');
            $table->string('current_payment_status',5)->nullable();
            $table->timestamps();

            $table->softDeletes();
            $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_applications');
    }
}
