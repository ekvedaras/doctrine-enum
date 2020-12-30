# Doctrine Enum

![Tests](https://github.com/ekvedaras/doctrine-enum/workflows/run-tests/badge.svg)
[![Code Coverage](https://img.shields.io/codecov/c/gh/ekvedaras/doctrine-enum/main?style=flat)](https://app.codecov.io/gh/ekvedaras/doctrine-enum)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/ekvedaras/doctrine-enum.svg?style=flat)](https://packagist.org/packages/ekvedaras/doctrine-enum)
[![Total Downloads](https://img.shields.io/packagist/dt/ekvedaras/doctrine-enum.svg?style=flat)](https://packagist.org/packages/ekvedaras/doctrine-enum)

<img src="logo.svg" width="192" height="192"/>

![Twitter Follow](https://img.shields.io/twitter/follow/ekvedaras?style=plastic)

This package integrates [ekvedaras/php-enum](https://github.com/ekvedaras/php-enum)
into Doctrine by
providing [custom enum mapping type](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/custom-mapping-types.html#custom-mapping-types).

## Usage

**PaymentStatus.php**

```php
namespace App\Enums;

use EKvedaras\Doctrine\Enum;

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
```

**UserStatus.php**

```php
namespace App\Enums;

use EKvedaras\Doctrine\Enum;

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
}
```

### Casting

**Payment.php**

```php
use App\Enums\PaymentStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="payments")
 */
class Payment
{
    // ...
   
    /**
     * @var PaymentStatus
     *
     * @ORM\Column(type=PaymentStatus::class)
     */
    protected $status;

    // ...
}
```

**User.php**

```php
use App\Enums\UserStatus;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User
{
    // ...
   
    /**
     * @var UserStatus
     *
     * @ORM\Column(type="user-status")
     */
    protected $status;

    // ...
}
```

Registering enum:

```php
use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use EKvedaras\DoctrineEnum\EnumType;

// As class name
EnumType::register(PaymentStatus::class);
EnumType::register('user-status', UserStatus::class);

// Or multiple at once
EnumType::register([
    PaymentStatus::class,
    'user-status' => UserStatus::class,
]);
```
