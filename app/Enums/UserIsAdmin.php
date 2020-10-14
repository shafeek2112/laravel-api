<?php
declare(strict_types=1);

namespace App\Enums;

/* 
* For to easy maintaining the ENUM
*/
abstract class UserIsAdmin extends Enum
{
    const USER_IS_ADMIN = 'Y';
    const USER_IS_NOT_ADMIN = 'N';
}
