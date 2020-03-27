<?php


namespace Uginroot\DoctrineTypeLocation;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use ReflectionClass;
use ReflectionException;
use Uginroot\PhpLocation\Location;

class LocationDoctrineType extends Type
{
    public const TYPE = 'point';

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform):bool
    {
        return true;
    }


    /**
     * @inheritDoc
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform):string
    {
        return strtoupper(static::TYPE);
    }


    /**
     * @param null|Location $value
     * @param AbstractPlatform $platform
     * @return mixed|string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if($value instanceof Location){
            return sprintf('POINT(%f %f)', $value->getLatitude(), $value->getLongitude());
        }

        return $value;
    }

    /**
     * @param null|Location $value
     * @param AbstractPlatform $platform
     * @return mixed|Location
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if($value !== null){
            [$latitude, $longitude] = sscanf($value, 'POINT(%f %f)');
            return new Location($latitude, $longitude);
        }
        return $value;
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function getName():string
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

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToPHPValueSQL($value, $platform): string
    {
        return sprintf('AsText(%s)', $value);
    }

    /**
     * @param string $sqlExpr
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('PointFromText(%s)', $sqlExpr);
    }
}