<?php
declare(strict_types=1);

namespace App\Enums;

/* 
* For to easy maintaining the ENUM
*/
abstract class UserStatus extends Enum
{
    const APPROVED  = 'approved';
    const PENDING   = 'pending';
    const REJECTED  = 'rejected';
}
