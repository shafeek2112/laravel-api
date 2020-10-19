<?php

namespace Database\Seeders;

use App\Enums\LoanStatus;
use Illuminate\Database\Seeder;
use App\Models\LoanApplication;
use App\Models\LoanRepaymentDetail;


class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LoanApplication::create([
            'user_id'                           => 3,
            'application_no'                    => 'LA-00000001',
            'loan_term'                         => LoanStatus::LOAN_TERM_SHORT,
            'repayment_frequency'               => LoanStatus::REPAYMENT_FREQUENCY_WEEKLY,
            'loan_amount'                       => 15000,
            'approved_status'                   => LoanStatus::PENDING,
            'application_date'                  => date('Y-m-d'),
        ]);
        LoanApplication::create([
            'user_id'                           => 3,
            'application_no'                    => 'LA-00000002',
            'loan_term'                         => LoanStatus::LOAN_TERM_SHORT,
            'repayment_frequency'               => LoanStatus::REPAYMENT_FREQUENCY_WEEKLY,
            'loan_amount'                       => 15000,
            'approved_status'                   => LoanStatus::PENDING,
            'application_date'                  => date('Y-m-d'),
        ]);

        LoanRepaymentDetail::create([
            'user_id'               => 3,
            'loan_application_id'   => '1',
            'loan_repayment_amount' => 289,
            'status'                => LoanStatus::PENDING,
        ]);
        LoanRepaymentDetail::create([
            'user_id'               => 3,
            'loan_application_id'   => '2',
            'loan_repayment_amount' => 289,
            'status'                => LoanStatus::PAYMENT_STATUS_PAYMENT_PROCESSING,
        ]);
    }
}
