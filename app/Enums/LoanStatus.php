<?php
declare(strict_types=1);

namespace App\Enums;

/* 
* For to easy maintaining the ENUM
*/
abstract class LoanStatus extends Enum
{
    const APPROVED = 'A';
    const PENDING = 'P';
    const REJECTED = 'R';
    const NEED_ADDITIONAL_INFO = 'N';
    
    const AWAITING_PAYMENT = 'AW';
    const NO_OUTSTANDING_PAYMENT = 'NO';
    const PAYMENT_FAILED = 'PF';
    const OVER_DUE = 'OD';

    const REPAYMENT_FREQUENCY_WEEKLY = 'weekly';
    const REPAYMENT_FREQUENCY_MONTHLY = 'monthly';
    const REPAYMENT_FREQUENCY_YEARLY = 'yearly';

    const LOAN_TERM_SHORT = '12Months';
    const LOAN_TERM_MEDIUM  = '36Months';
    const LOAN_TERM_LONG = '64Months'; 
}
