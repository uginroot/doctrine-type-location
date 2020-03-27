<?php


namespace Uginroot\DoctrineTypeLocation\Test;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Uginroot\DoctrineTypeLocation\Exceptions\UnsupportedPlatformException;
use Uginroot\DoctrineTypeLocation\LocationDoctrineType;
use Uginroot\PhpLocation\Location;

class LocationDoctrineTypeTest extends TestCase
{
    private ?LocationDoctrineType $type;

    /**
     * @throws DBALException
     * @throws ReflectionException
     * @throws ReflectionException
     */
    public static function setUpBeforeClass():void
    {
        $class = new ReflectionClass(LocationDoctrineType::class);
        $classLocation = new ReflectionClass(Location::class);
        Type::addType($classLocation->getShortName(), $class->getName());
    }

    /**
     * @throws DBALException
     * @throws ReflectionException
     */
    protected function setUp():void
    {
        $class = new ReflectionClass(Location::class);
        $type = Type::getType($class->getShortName());
        if($type instanceof LocationDoctrineType){
            $this->type = $type;
        }
    }

    /**
     * @return array
     */
    public function providerConvertToDataBaseValue():array
    {
        return [
            'null' => [null, null],
            'int' => [new Location(1, 1), 'POINT(1.000000 1.000000)'],
            'float' => [new Location(55.7539, 37.6208), 'POINT(55.753900 37.620800)'],
        ];
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider providerConvertToDataBaseValue
     */
    public function testConvertToDataBaseValue(?Location $value, ?string $expected):void
    {
        $this->assertSame($expected, $this->type->convertToDatabaseValue($value, new MySqlPlatform()));
    }

    /**
     * @param $expected
     * @param $value
     * @dataProvider providerConvertToDataBaseValue
     */
    public function testConvertToPhpValue(?Location $expected, ?string $value):void
    {
        /** @var null|Location $result */
        $result = $this->type->convertToPHPValue($value, new MySqlPlatform());
        if($expected instanceof Location){
            $this->assertInstanceOf(Location::class, $result);
            $this->assertSame($expected->getLatitude(), $result->getLatitude());
            $this->assertSame($expected->getLongitude(), $result->getLongitude());
        } else {
            $this->assertSame($expected, $result);
        }
    }

    public function testGetSqlDeclaration():void
    {
        $actual = $this->type->getSQLDeclaration([], new MySqlPlatform());
        $this->assertSame('point', $actual);
    }

    public function testRequiresSqlCommentHint():void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint(new MySqlPlatform()));
    }

    public function testUnsupportedPlatformExceptionHint():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->requiresSQLCommentHint($platform);
    }

    public function testUnsupportedPlatformExceptionDeclaration():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->getSQLDeclaration([], $platform);
    }

    public function testUnsupportedPlatformExceptionToPhpValue():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->convertToPHPValue('POINT(1 1)', $platform);
    }

    public function testUnsupportedPlatformExceptionToDatabaseValue():void
    {
        $platform = new DB2Platform;
        $this->expectException(UnsupportedPlatformException::class);
        $this->type->convertToDatabaseValue(new Location(1, 1), $platform);
    }

    public function testGetMappedDatabaseTypes():void
    {
        $platform = new MySqlPlatform();
        $types = $this->type->getMappedDatabaseTypes($platform);
        $this->assertContains('point', $types);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetName():void
    {
        $this->assertSame('Location', $this->type->getName());
    }
}