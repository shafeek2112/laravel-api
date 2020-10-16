<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LoanStatus;

class CreateLoanRepaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_repayment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('loan_application_id');
            $table->decimal('loan_repayment_amount', 8, 2);
            $table->string('status',10)->default(LoanStatus::PAYMENT_STATUS_PENDING);
            $table->date('payment_date')->nullable();
            $table->timestamps();

            $table->softDeletes();
            $table->index('user_id');
            $table->index('loan_application_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_repayment_details');
    }
}
