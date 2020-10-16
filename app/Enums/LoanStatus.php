<?php
declare(strict_types=1);

namespace App\Enums;

/* 
* For to easy maintaining the ENUM
*/
abstract class LoanStatus extends Enum
{
    ################ Loan Status
    const APPROVED = 'approved';
    const PENDING = 'pending';
    const REJECTED = 'rejected';
    const NEED_ADDITIONAL_INFO = 'need_info';
    
    ################ Loan Payment Status
    const AWAITING_PAYMENT = 'AW';
    const NO_OUTSTANDING_PAYMENT = 'NO';
    const PAYMENT_FAILED = 'PF';
    const OVER_DUE = 'OD';
    
    ################ Loan RePayment Frequency
    const REPAYMENT_FREQUENCY_WEEKLY = 'weekly';
    const REPAYMENT_FREQUENCY_WEEKLY_INT = 7;
    
    const REPAYMENT_FREQUENCY_MONTHLY = 'monthly';
    const REPAYMENT_FREQUENCY_MONTHLY_INT = 30;

    const REPAYMENT_FREQUENCY_YEARL = 'yearly';
    const REPAYMENT_FREQUENCY_YEARL_INT = 365;
    
    const REPAYMENT_FREQUENCY_DEFAULT = 7;
    
    ################ Loan Term
    const LOAN_TERM_SHORT = '12Months';
    const LOAN_TERM_SHORT_INT = 12;
    
    const LOAN_TERM_MEDIUM  = '36Months';
    const LOAN_TERM_MEDIUM_INT  = 36;
    
    const LOAN_TERM_LONG = '64Months'; 
    const LOAN_TERM_LONG_INT = 64; 
    
    const LOAN_TERM_DEFAULT = 12;
    const TOTAL_DAYS_IN_MONTH = 30; // For simplicity

     ################ LoanRepayment Payment status
     const PAYMENT_STATUS_PAID = 'paid';
     const PAYMENT_STATUS_FAILED = 'failed';
     const PAYMENT_STATUS_PAYMENT_PROCESSING = 'processing';
     const PAYMENT_STATUS_PENDING = 'pending';
     const PAYMENT_STATUS_ALL = 'all';
}
