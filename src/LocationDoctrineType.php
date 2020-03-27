<?php


namespace Uginroot\DoctrineTypeLocation;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use ReflectionClass;
use ReflectionException;
use Uginroot\DoctrineTypeLocation\Exceptions\UnsupportedPlatformException;
use Uginroot\PhpLocation\Location;

class LocationDoctrineType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform):bool
    {
        if ($platform instanceof MySqlPlatform) {
            return true;
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }


    /**
     * @inheritDoc
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform):string
    {

        if ($platform instanceof MySqlPlatform) {
            return 'point';
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }


    /**
     * @param null|Location $value
     * @param AbstractPlatform $platform
     * @return mixed|string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($platform instanceof MySqlPlatform) {
            return $value->toPoint();
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }

    /**
     * @param null|Location $value
     * @param AbstractPlatform $platform
     * @return mixed|Location
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof Location) {
            return $value;
        }

        if ($platform instanceof MySqlPlatform) {
            return Location::createFromPoint($value);
        }

        throw new UnsupportedPlatformException(sprintf('Platform %s not support', get_class($platform)));
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getName()
    {
        $reflectionClass = new ReflectionClass(Location::class);
        return $reflectionClass->getShortName();
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        $types = parent::getMappedDatabaseTypes($platform);

        if ($platform instanceof MySqlPlatform) {
            $platformType = 'point';
            if(!in_array($platformType, $types)){
                $types[] = $platformType;
            }
        }

        return $types;
    }
}