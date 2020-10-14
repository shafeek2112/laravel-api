<?php
declare(strict_types=1);

namespace App\Enums;

/**
 * The class defining the constants for the application
 */
abstract class Enum
{
    public static $consts = [];

    public static function getConstants()
    {
        $calledClass = get_called_class();
        if (!empty(self::$consts[$calledClass])) {
            return self::$consts[$calledClass];
        }

        $reflect = new \ReflectionClass($calledClass);
        self::$consts[$calledClass] = $reflect->getConstants();

        return self::$consts[$calledClass];
    }

}
