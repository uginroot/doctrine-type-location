# Php doctrine type location

Doctrine location type from this [Location](https://github.com/uginroot/php-location) class

## Install
```bash
composer request uginroot/doctrine-type-location:^1.2
```

## Example

### Registration type
```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            Location: 'Uginroot\DoctrineTypeLocation\LocationDoctrineType'
```

### Use type
```php

use Uginroot\PhpLocation\Location;
use Doctrine\ORM\Mapping as ORM;

class User{
    // ...

    /**
     * @ORM\Column(type="Location")
     * @var Location|null
     */
    private $location;

    /**
     * @return Location|null
     */
    public  function getLocation(): ?Location{
        return $this->location;
    }
    
    /**
     * @param Location|null $location
     * @return $this
     */
    public  function setLocation(?Location $location):self {
        $this->location = $location;
        return $this;
    }
}
```
