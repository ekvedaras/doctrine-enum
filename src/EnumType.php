<?php

namespace EKvedaras\DoctrineEnum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use EKvedaras\PHPEnum\BaseEnum;
use InvalidArgumentException;

/**
 * Class EnumType
 * @package EKvedaras\DoctrineEnum
 */
class EnumType extends Type
{
    /** @var string|null */
    protected $name;

    /** @var BaseEnum */
    protected $class;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name ?? 'enum';
    }

    /**
     * @inheritDoc
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * @param int|string       $value
     * @param AbstractPlatform $platform
     * @return BaseEnum|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?BaseEnum
    {
        if ($value === null) {
            return null;
        }

        return $this->class::from($value);
    }

    /**
     * @param int|string|BaseEnum $value
     * @param AbstractPlatform    $platform
     * @return int|mixed|string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (($isObject = is_object($value)) && !$value instanceof $this->class) {
            throw new InvalidArgumentException("The given value is not an instance of {$this->class}.");
        }

        return $isObject ? $value->id() : $this->class::from($value)->id();
    }

    /**
     * @param             $typesOrNameOrClass
     * @param string|null $class
     * @throws Exception
     */
    public static function register($typesOrNameOrClass, ?string $class = null): void
    {
        if (!is_array($typesOrNameOrClass)) {
            $typesOrNameOrClass = [$typesOrNameOrClass => $class];
        }

        foreach ($typesOrNameOrClass as $nameOrClass => $class) {
            $name  = is_int($nameOrClass) ? $class : $nameOrClass;
            $class = $class ?? $nameOrClass;

            if (!is_subclass_of($class, BaseEnum::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Provided enum class "%s" is not valid. Enums must extend one of "%s" children.',
                    $class,
                    BaseEnum::class
                ));
            }

            self::addType($name, static::class);

            /** @var static $type */
            $type        = self::getType($name);
            $type->name  = $name;
            $type->class = $class;
        }
    }
}
