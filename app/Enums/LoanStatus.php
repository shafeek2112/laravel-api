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
}
