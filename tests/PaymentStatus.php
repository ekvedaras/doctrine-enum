<?php

namespace Tests;

use EKvedaras\PHPEnum\Doctrine\Enum;

/**
 * Class PaymentStatus
 * @package Tests
 */
class PaymentStatus extends Enum
{
    /**
     * @return static
     */
    final public static function pending(): self
    {
        return static::get('pending', 'Payment is pending');
    }

    /**
     * @return static
     */
    final public static function completed(): self
    {
        return static::get('completed', 'Payment has been processed');
    }

    /**
     * @return static
     */
    final public static function failed(): self
    {
        return static::get('failed', 'Payment has failed');
    }
}
