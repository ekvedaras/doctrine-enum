<?php

namespace Tests;

use EKvedaras\PHPEnum\Doctrine\Enum;

/**
 * Class UserStatus
 * @package Tests
 */
class UserStatus extends Enum
{
    /**
     * @return static
     */
    final public static function active(): self
    {
        return static::get(1, 'User is active');
    }

    /**
     * @return static
     */
    final public static function banned(): self
    {
        return static::get(2, 'User is banned');
    }

    /**
     * @return static
     */
    final public static function deactivated(): self
    {
        return static::get(3, 'User account is deactivated');
    }

    /**
     * @return static
     */
    final public static function pending(): self
    {
        return static::get('pending', 'User account is pending activation');
    }
}
