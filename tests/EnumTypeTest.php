<?php

namespace Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;
use EKvedaras\DoctrineEnum\EnumType;
use EKvedaras\PHPEnum\BaseEnum;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * Class EnumTypeTest
 * @package Tests
 */
class EnumTypeTest extends TestCase
{
    /** @var AbstractPlatform */
    private static $platform;

    public static function setUpBeforeClass(): void
    {
        static::$platform = new SqlitePlatform();
    }

    protected function setUp(): void
    {
        if (method_exists(Type::class, 'getTypeRegistry')) {
            $typeRegistry  = Type::getTypeRegistry();
            $ref           = new ReflectionObject($typeRegistry);
            $instancesProp = $ref->getProperty('instances');
            $instancesProp->setAccessible(true);
            $instancesProp->setValue($typeRegistry, []);
        } else {
            if (Type::hasType(PaymentStatus::class)) {
                Type::overrideType(PaymentStatus::class, null);
            }

            if (Type::hasType('user-status')) {
                Type::overrideType('user-status', null);
            }
        }
    }

    /** @test */
    public function it_registers_types(): void
    {
        $this->assertFalse(Type::hasType(PaymentStatus::class));
        $this->assertFalse(Type::hasType('user-status'));

        EnumType::register(PaymentStatus::class);
        EnumType::register('user-status', UserStatus::class);

        $this->assertTrue(Type::hasType(PaymentStatus::class));
        $this->assertTrue(Type::hasType('user-status'));
    }

    /** @test */
    public function it_registers_multiple_types(): void
    {
        $this->assertFalse(Type::hasType(PaymentStatus::class));
        $this->assertFalse(Type::hasType('user-status'));

        EnumType::register([
            'user-status' => UserStatus::class,
            PaymentStatus::class,
        ]);

        $this->assertTrue(Type::hasType(PaymentStatus::class));
        $this->assertTrue(Type::hasType('user-status'));
    }

    /** @test */
    public function it_sets_correct_name_and_class(): void
    {
        $this->assertFalse(Type::hasType(PaymentStatus::class));
        $this->assertFalse(Type::hasType('user-status'));

        EnumType::register([
            PaymentStatus::class,
            'user-status' => UserStatus::class,
        ]);

        $paymentStatus = Type::getType(PaymentStatus::class);
        $this->assertInstanceOf(EnumType::class, $paymentStatus);
        $this->assertEquals(PaymentStatus::class, $paymentStatus->getName());

        $userStatus = Type::getType('user-status');
        $this->assertInstanceOf(EnumType::class, $userStatus);
        $this->assertEquals('user-status', $userStatus->getName());
    }

    /** @test */
    public function it_only_allows_registering_subclasses_of_base_enum(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(sprintf(
            'Provided enum class "%s" is not valid. Enums must extend one of "%s" children.',
            static::class,
            BaseEnum::class
        )));

        EnumType::register(static::class);
    }

    /** @test */
    public function it_casts_enum_attributes(): void
    {
        EnumType::register(PaymentStatus::class);
        $type = EnumType::getType(PaymentStatus::class);

        $this->assertNull($type->convertToPHPValue(null, static::$platform));
        $this->assertNull($type->convertToDatabaseValue(null, static::$platform));

        $this->assertSame(PaymentStatus::completed(), $type->convertToPHPValue(PaymentStatus::completed()->id(), static::$platform));
        $this->assertEquals(PaymentStatus::completed()->id(), $type->convertToDatabaseValue(PaymentStatus::completed(), static::$platform));
        $this->assertEquals(PaymentStatus::completed()->id(), $type->convertToDatabaseValue(PaymentStatus::completed()->id(), static::$platform));
    }

    /** @test */
    public function it_prevents_invalid_values(): void
    {
        EnumType::register(PaymentStatus::class);
        $type         = EnumType::getType(PaymentStatus::class);
        $invalidValue = 'invalid';

        $this->expectExceptionObject(
            new OutOfBoundsException(PaymentStatus::class . "::from({$invalidValue}): given id doesn't exist on this enumerable type.")
        );

        $type->convertToDatabaseValue($invalidValue, static::$platform);
    }

    /** @test */
    public function it_does_not_allow_to_set_same_value_from_other_enum_class(): void
    {
        EnumType::register(PaymentStatus::class);
        EnumType::register(UserStatus::class);
        $type = EnumType::getType(PaymentStatus::class);

        $this->expectExceptionObject(
            new InvalidArgumentException('The given value is not an instance of ' . PaymentStatus::class . '.')
        );

        $type->convertToDatabaseValue(UserStatus::pending(), static::$platform);
    }

    /** @test */
    public function it_requires_sql_comment_hint(): void
    {
        EnumType::register(PaymentStatus::class);
        $type = EnumType::getType(PaymentStatus::class);

        $this->assertTrue($type->requiresSQLCommentHint(static::$platform));
    }

    /** @test */
    public function getSQLDeclarationReturnsValueFromPlatform(): void
    {
        EnumType::register(PaymentStatus::class);
        $type = EnumType::getType(PaymentStatus::class);

        $this->assertEquals(static::$platform->getVarcharTypeDeclarationSQL([]), $type->getSQLDeclaration([], static::$platform));
    }
}
